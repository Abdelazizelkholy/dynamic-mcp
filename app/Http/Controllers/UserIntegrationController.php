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
use App\Repositories\IntegrationServiceResponseRepositoryInterface;
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
        private readonly IntegrationServiceResponseRepositoryInterface $responseRepo,
    ) {}

    // GET /user-integrations/services/{serviceId}/execute — authenticated via the
    // `api-key` header (see ApiKeyAuth middleware), not a standard Bearer token.
    // Tells the caller whether the current user is already connected to this
    // service's integration, the service/integration/user context needed to
    // render a connect UI, and this service's input groups/standalone inputs.
    public function execute(Request $request, int $serviceId): JsonResponse
    {
        $service = IntegrationService::with('integration')->findOrFail($serviceId);
        $integration = $service->integration;
        $integrationId = $integration->id;
        $user = $request->user();

        $userIntegration = $this->repo->findByUserAndIntegration($user->id, $integrationId);

        $authSteps = IntegrationAuthStep::where('integration_id', $integrationId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $serviceResponse = $this->responseRepo->findByService($serviceId);

        return ApiResponse::success([
            'service' => [
                'id'              => $service->id,
                'service_name_en' => $service->service_name_en,
                'service_name_ar' => $service->service_name_ar,
                'description_en'  => $service->description_en,
                'description_ar'  => $service->description_ar,
                // Effective base URL used when calling this service.
                'base_url'        => $service->base_url_override ?: $integration->base_api_url,
            ],
            'integration' => [
                'id'             => $integration->id,
                'name'           => $integration->name,
                'description_en' => $integration->description_en,
                'description_ar' => $integration->description_ar,
                'image'          => $integration->media_url,
            ],
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'image' => $user->profile_picture_url,
            ],
            'output_example'   => $serviceResponse->response_example ?? [],
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

    // POST /user-integrations/{integrationId}/connect — authenticated via the
    // `api-key` header (see ApiKeyAuth middleware), not a standard Bearer token.
    // If the user is already connected to this integration, this instead re-runs
    // its refresh_token auth step (see AuthStepRunner::connect()/refresh()).
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
