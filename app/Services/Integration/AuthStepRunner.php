<?php

namespace App\Services\Integration;

use App\Events\UserIntegrationConnected;
use App\Models\Integration;
use App\Models\IntegrationAuthStep;
use App\Models\User;
use App\Models\UserIntegration;
use App\Models\UserIntegrationAuthLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Executes an Integration's admin-defined auth steps (IntegrationAuthStep) at
 * runtime on behalf of a specific user, persisting the result on UserIntegration.
 */
class AuthStepRunner
{
    private const STATE_TTL_MINUTES = 15;
    private const SECRET_KEYS = ['client_secret', 'refresh_token', 'access_token', 'password'];

    /**
     * Starts (or resumes) the auth flow for a user against this integration.
     * - already connected AND a refresh_token step exists -> re-runs it (see refresh()).
     * - already connected but no refresh_token step        -> re-runs the normal flow below
     *   (re-authenticate from scratch, e.g. re-submitting set_credentials).
     * - auth_type = redirect     -> returns a redirect_url the frontend must send the user to.
     * - auth_type = call         -> executes immediately (e.g. set_credentials) using $userInputs.
     */
    public function connect(Integration $integration, User $user, array $userInputs = []): array
    {
        $userIntegration = UserIntegration::firstOrCreate(
            ['user_id' => $user->id, 'integration_id' => $integration->id],
            ['status' => 'pending']
        );

        if ($userIntegration->status === 'connected' && $this->hasRefreshStep($integration)) {
            return $this->refresh($userIntegration);
        }

        $step = $integration->authSteps()->where('is_active', true)->orderBy('order')->first();

        if (! $step) {
            throw new RuntimeException('This integration has no active auth steps configured.');
        }

        if ($step->auth_type === 'redirect') {
            return $this->buildRedirect($step, $userIntegration, $userInputs);
        }

        $this->runCallStep($step, $userIntegration, ['user' => $userInputs, 'front' => []]);

        return ['user_integration' => $userIntegration->fresh(), 'redirect_url' => null];
    }

    /**
     * Resumes the flow after the provider redirects back with ?code&state.
     */
    public function handleCallback(Integration $integration, string $state, array $queryParams): UserIntegration
    {
        $cached = Cache::pull("integration_auth_state:{$state}");

        if (! $cached || (int) $cached['integration_id'] !== $integration->id) {
            throw new RuntimeException('Invalid or expired state.');
        }

        $userIntegration = UserIntegration::findOrFail($cached['user_integration_id']);

        $nextStep = $integration->authSteps()
            ->where('is_active', true)
            ->where('order', '>', $cached['step_order'])
            ->orderBy('order')
            ->first();

        if (! $nextStep) {
            throw new RuntimeException('No follow-up auth step is configured after the redirect step.');
        }

        // Merge what the user submitted at connect()-time (e.g. client_id/client_secret/
        // redirect_uri) with the callback's own query params (code, state, ...) — Salla
        // never resends secrets on the redirect, so the follow-up call needs both sources.
        $userContext = array_merge($cached['user_inputs'] ?? [], $queryParams);

        $this->runCallStep($nextStep, $userIntegration, ['user' => $userContext, 'front' => $queryParams]);

        UserIntegrationConnected::dispatch($userIntegration->fresh());

        return $userIntegration->fresh();
    }

    private function hasRefreshStep(Integration $integration): bool
    {
        return $integration->authSteps()
            ->where('is_active', true)
            ->where('step_type', 'refresh_token')
            ->exists();
    }

    private function refresh(UserIntegration $userIntegration): array
    {
        $step = $userIntegration->integration->authSteps()
            ->where('is_active', true)
            ->where('step_type', 'refresh_token')
            ->orderBy('order')
            ->first();

        if (! $step) {
            throw new RuntimeException('This integration has no refresh_token auth step configured.');
        }

        $this->runCallStep($step, $userIntegration, ['user' => [], 'front' => []]);

        return ['user_integration' => $userIntegration->fresh(), 'redirect_url' => null];
    }

    // ── Internals ──────────────────────────────────────────────────────────

    private function buildRedirect(IntegrationAuthStep $step, UserIntegration $userIntegration, array $userInputs): array
    {
        $state = Str::random(40);

        $resolved = $this->resolveInputs($step, $userIntegration, [
            'user'  => $userInputs,
            'front' => ['state' => $state],
        ]);

        Cache::put("integration_auth_state:{$state}", [
            'user_integration_id' => $userIntegration->id,
            'integration_id'      => $step->integration_id,
            'step_order'          => $step->order,
            'user_inputs'         => $userInputs,
        ], now()->addMinutes(self::STATE_TTL_MINUTES));

        $query = array_merge($resolved['params'], $resolved['body'], $resolved['headers']);

        return [
            'redirect_url'     => $step->base_endpoint_url.'?'.http_build_query($query),
            'state'            => $state,
            'user_integration' => $userIntegration,
        ];
    }

    private function runCallStep(IntegrationAuthStep $step, UserIntegration $userIntegration, array $context): void
    {
        $context['previous'] = $userIntegration->credentials ?? [];

        $resolved = $this->resolveInputs($step, $userIntegration, $context);

        // GET/DELETE have no body — fold body values into the query string instead.
        if (in_array(strtoupper($step->http_method), ['GET', 'DELETE'], true)) {
            $resolved['params'] = array_merge($resolved['params'], $resolved['body']);
            $resolved['body'] = [];
        }

        $url = $step->base_endpoint_url;
        if (! empty($resolved['params'])) {
            $url .= '?'.http_build_query($resolved['params']);
        }

        try {
            $response = Http::asForm()
                ->withHeaders($resolved['headers'])
                ->send($step->http_method, $url, ['form_params' => $resolved['body']]);
        } catch (Throwable $e) {
            $this->logStep($userIntegration, $step, $resolved, null, 'failed', $e->getMessage());
            $userIntegration->update(['status' => 'error']);
            throw $e;
        }

        $body = $response->json() ?? [];

        if (! $response->successful()) {
            $this->logStep($userIntegration, $step, $resolved, $body, 'failed', "HTTP {$response->status()}");
            $userIntegration->update(['status' => 'error', 'last_response' => $body]);
            throw new RuntimeException("Auth step '{$step->name}' failed: HTTP {$response->status()}");
        }

        $this->logStep($userIntegration, $step, $resolved, $body, 'success', null);

        $credentials = array_merge(
            $userIntegration->credentials ?? [],
            array_intersect_key($body, array_flip($step->outputs ?? []))
        );

        $expiresAt = null;
        if (isset($body['expires'])) {
            $expiresAt = now()->addSeconds((int) $body['expires']);
        } elseif (isset($body['expires_in'])) {
            $expiresAt = now()->addSeconds((int) $body['expires_in']);
        }

        $userIntegration->update([
            'status'             => 'connected',
            'credentials'        => $credentials,
            'last_response'      => $body,
            'expires_at'         => $expiresAt,
            'connected_at'       => $userIntegration->connected_at ?? now(),
            'last_refreshed_at'  => $step->step_type === 'refresh_token' ? now() : $userIntegration->last_refreshed_at,
        ]);
    }

    /**
     * Resolves every input of a step into ['body' => [...], 'params' => [...], 'headers' => [...]]
     * based on each input's require_from.
     */
    private function resolveInputs(IntegrationAuthStep $step, UserIntegration $userIntegration, array $context): array
    {
        $resolved = ['body' => [], 'params' => [], 'headers' => []];

        foreach ($step->inputs ?? [] as $input) {
            // Guards against legacy rows where `inputs` was stored as a flat
            // { "key": "value" } object instead of an array of input objects.
            if (! is_array($input) || ! isset($input['key'])) {
                continue;
            }

            $key    = $input['key'];
            $bucket = $input['type'] ?? 'body'; // body | params | headers

            $value = match ($input['require_from'] ?? 'admin') {
                'admin'                  => $this->resolveAdminValue($input['value'] ?? null),
                'user'                   => $context['user'][$key] ?? null,
                'front'                  => $context['front'][$key] ?? null,
                'response'               => $context['response'][$key] ?? null,
                'user_integration'       => data_get($userIntegration->credentials, $input['value'] ?? $key),
                'previous_step_response' => data_get($context['previous'] ?? [], $input['value'] ?? $key),
                default                  => null,
            };

            if ($value === null) {
                continue;
            }

            $resolved[$bucket][$key] = $value;
        }

        return $resolved;
    }

    /**
     * admin-sourced values normally come from the literal `value` stored on the
     * input. A value of the form "config:services.salla.client_id" is instead
     * resolved from Laravel config/.env, so real secrets don't have to live as
     * plaintext in the integration_auth_steps table.
     */
    private function resolveAdminValue(?string $value): ?string
    {
        if ($value !== null && str_starts_with($value, 'config:')) {
            return config(substr($value, 7));
        }

        return $value;
    }

    private function logStep(
        UserIntegration $userIntegration,
        IntegrationAuthStep $step,
        array $request,
        ?array $response,
        string $status,
        ?string $error
    ): void {
        UserIntegrationAuthLog::create([
            'user_integration_id'      => $userIntegration->id,
            'integration_auth_step_id' => $step->id,
            'status'                   => $status,
            'request'                  => $this->maskSecrets($request),
            'response'                 => $response,
            'error_message'            => $error,
            'executed_at'              => now(),
        ]);
    }

    private function maskSecrets(array $resolved): array
    {
        foreach (['body', 'params', 'headers'] as $bucket) {
            foreach (self::SECRET_KEYS as $secretKey) {
                if (isset($resolved[$bucket][$secretKey])) {
                    $resolved[$bucket][$secretKey] = '***';
                }
            }
        }

        return $resolved;
    }
}
