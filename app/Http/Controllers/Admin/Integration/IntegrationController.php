<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Integration\StoreIntegrationRequest;
use App\Http\Requests\Admin\Integration\UpdateIntegrationRequest;
use App\Http\Resources\Admin\Integration\IntegrationResource;
use App\Repositories\IntegrationRepositoryInterface;

class IntegrationController extends Controller
{
    public function __construct(private IntegrationRepositoryInterface $repo) {}

    public function index()
    {
        return IntegrationResource::collection($this->repo->all());
    }

    public function store(StoreIntegrationRequest $request)
    {
        $integration = $this->repo->create($request->validated());
        return new IntegrationResource($integration);
    }

    public function show($id)
    {
        $integration = $this->repo->find($id);
        if (!$integration) return response()->json(['message'=>'Not found'],404);

        return new IntegrationResource($integration);
    }

    public function update(UpdateIntegrationRequest $request, $id)
    {
        $integration = $this->repo->update($id, $request->validated());
        if (!$integration) return response()->json(['message'=>'Not found'],404);

        return new IntegrationResource($integration);
    }

    public function destroy($id)
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) return response()->json(['message'=>'Not found'],404);

        return response()->json(['message'=>'Deleted successfully']);
    }
}
