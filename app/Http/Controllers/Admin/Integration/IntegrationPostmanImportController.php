<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Integration\ImportPostmanCollectionRequest;
use App\Http\Resources\Admin\IntegrationService\IntegrationServiceResource;
use App\Models\Integration;
use App\Services\Integration\PostmanCollectionImporter;
use Illuminate\Http\JsonResponse;

class IntegrationPostmanImportController extends Controller
{
    public function __construct(private readonly PostmanCollectionImporter $importer) {}

    // POST /admin/integrations/{id}/import-postman
    public function import(ImportPostmanCollectionRequest $request, int $id): JsonResponse
    {
        $integration = Integration::findOrFail($id);

        if ($request->hasFile('file')) {
            $collection = json_decode(file_get_contents($request->file('file')->getRealPath()), true);

            if (! is_array($collection)) {
                return ApiResponse::error('The uploaded file is not valid JSON.', 422);
            }
        } else {
            $collection = $request->input('collection');
        }

        if (empty($collection['item'])) {
            return ApiResponse::error('This does not look like a Postman Collection (missing "item").', 422);
        }

        $services = $this->importer->import($integration, $collection);

        return ApiResponse::success(
            IntegrationServiceResource::collection(collect($services)),
            count($services).' service(s) imported successfully.',
            201
        );
    }
}
