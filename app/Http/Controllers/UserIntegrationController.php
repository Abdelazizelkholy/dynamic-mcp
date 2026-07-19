<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\UserIntegration\ConnectUserIntegrationRequest;
use App\Http\Resources\Admin\AuthStep\IntegrationAuthStepResource;
use App\Http\Resources\Admin\ServiceInput\IntegrationServiceInputGroupResource;
use App\Http\Resources\Admin\ServiceInput\IntegrationServiceInputResource;
use App\Http\Resources\UserIntegration\UserIntegrationInfoResource;
use App\Http\Resources\UserIntegration\UserIntegrationResource;
use App\Models\Integration;
use App\Models\IntegrationAuthStep;
use App\Models\IntegrationService;
use App\Repositories\IntegrationServiceInputGroupRepositoryInterface;
use App\Repositories\IntegrationServiceInputRepositoryInterface;
use App\Repositories\UserIntegrationRepositoryInterface;
use App\Services\Integration\AuthStepRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class UserIntegrationController extends Controller
{
    public function __construct(
        private readonly AuthStepRunner $runner,
        private readonly UserIntegrationRepositoryInterface $repo,
        private readonly IntegrationServiceInputGroupRepositoryInterface $groupRepo,
        private readonly IntegrationServiceInputRepositoryInterface $inputRepo,
    ) {}

    // GET /user-integrations/services/{serviceId}/status — auth required. Tells the
    // frontend whether the current user is already connected to this service's
    // integration, plus the integration's active auth steps and this service's
    // input groups/standalone inputs (so it knows what to collect/render).
    public function status(Request $request, int $serviceId): JsonResponse
    {
        $service = IntegrationService::findOrFail($serviceId);
        $integrationId = $service->integration_id;

        $userIntegration = $this->repo->findByUserAndIntegration($request->user()->id, $integrationId);

        $authSteps = IntegrationAuthStep::where('integration_id', $integrationId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return ApiResponse::success([
            'is_connected'     => $userIntegration?->status === 'connected',
            'user_integration' => $userIntegration ? [new UserIntegrationResource($userIntegration)] : [],
            'user_info'        => $userIntegration?->info ? new UserIntegrationInfoResource($userIntegration->info) : null,
            'auth_steps'       => IntegrationAuthStepResource::collection($authSteps),
            'groups'           => IntegrationServiceInputGroupResource::collection($this->groupRepo->allByService($serviceId)),
            'inputs'           => IntegrationServiceInputResource::collection($this->inputRepo->standaloneByService($serviceId)),
        ]);
    }

    // GET /user-integrations
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            UserIntegrationResource::collection($this->repo->allByUser($request->user()->id)),
            'Connected integrations retrieved successfully.'
        );
    }

    // GET /user-integrations/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $userIntegration = $this->repo->find($id);

        if (! $userIntegration || $userIntegration->user_id !== $request->user()->id) {
            return ApiResponse::error('User integration not found.', 404);
        }

        return ApiResponse::success(new UserIntegrationResource($userIntegration));
    }

    // POST /user-integrations/{integrationId}/connect
    public function connect(ConnectUserIntegrationRequest $request, int $integrationId): JsonResponse
    {
        $integration = Integration::findOrFail($integrationId);

        // Accepts either a nested `inputs: {...}` object, or the same fields
        // sent flat at the top level of the request body — both work.
        $userInputs = $request->has('inputs')
            ? (array) $request->input('inputs')
            : $request->except('inputs');

        try {
            $result = $this->runner->connect($integration, $request->user(), $userInputs);
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        if (! empty($result['redirect_url'])) {
            return ApiResponse::success([
                'redirect_url' => $result['redirect_url'],
                'state'        => $result['state'],
            ], 'Redirect the user to this URL to complete authorization.');
        }

        return ApiResponse::success(
            new UserIntegrationResource($result['user_integration']),
            'Integration connected successfully.'
        );
    }

    // GET /user-integrations/{integrationId}/callback  (public — the provider redirects the browser here)
    public function callback(Request $request, int $integrationId): JsonResponse
    {
        $integration = Integration::findOrFail($integrationId);
        $state = $request->query('state');

        if (! $state) {
            return ApiResponse::error('Missing state parameter.', 422);
        }

        try {
            $userIntegration = $this->runner->handleCallback($integration, $state, $request->query());
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        return ApiResponse::success(
            new UserIntegrationResource($userIntegration),
            'Integration connected successfully.'
        );
    }

    // POST /user-integrations/{id}/refresh
    public function refresh(Request $request, int $id): JsonResponse
    {
        $userIntegration = $this->repo->find($id);

        if (! $userIntegration || $userIntegration->user_id !== $request->user()->id) {
            return ApiResponse::error('User integration not found.', 404);
        }

        try {
            $userIntegration = $this->runner->refresh($userIntegration);
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        return ApiResponse::success(new UserIntegrationResource($userIntegration), 'Token refreshed successfully.');
    }

    // DELETE /user-integrations/{id}
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userIntegration = $this->repo->find($id);

        if (! $userIntegration || $userIntegration->user_id !== $request->user()->id) {
            return ApiResponse::error('User integration not found.', 404);
        }

        $this->repo->delete($id);

        return ApiResponse::success(null, 'Integration disconnected.');
    }
}
