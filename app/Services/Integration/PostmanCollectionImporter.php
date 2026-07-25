<?php

namespace App\Services\Integration;

use App\Models\Integration;
use App\Models\IntegrationService;
use App\Repositories\IntegrationServiceHeaderRepositoryInterface;
use App\Repositories\IntegrationServiceInputRepositoryInterface;
use App\Repositories\IntegrationServiceRepositoryInterface;

/**
 * Bulk-creates IntegrationServices (+ headers + body inputs) for an Integration
 * from an exported Postman Collection (v2.1 format). Folders are flattened —
 * every request at any nesting level becomes one service, in encountered order.
 */
class PostmanCollectionImporter
{
    public function __construct(
        private readonly IntegrationServiceRepositoryInterface $serviceRepo,
        private readonly IntegrationServiceHeaderRepositoryInterface $headerRepo,
        private readonly IntegrationServiceInputRepositoryInterface $inputRepo,
    ) {}

    /**
     * @return IntegrationService[]
     */
    public function import(Integration $integration, array $collection): array
    {
        $items = $this->flatten($collection['item'] ?? []);

        $created = [];

        foreach ($items as $item) {
            $request = $item['request'] ?? null;

            if (! $request) {
                continue;
            }

            // Postman allows `request` to be a plain URL string for simple GETs.
            if (is_string($request)) {
                $request = ['method' => 'GET', 'url' => $request];
            }

            $method = strtoupper($request['method'] ?? 'GET');
            if (! in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                $method = 'POST';
            }

            $service = $this->serviceRepo->create([
                'integration_id'         => $integration->id,
                'service_name_en'        => $item['name'] ?? 'Untitled',
                'service_name_ar'        => $item['name'] ?? 'Untitled',
                'http_method'            => $method,
                'content_type'           => 'application/json',
                'endpoint_path'          => $this->extractEndpointPath($request['url'] ?? '/'),
                'is_enabled'             => true,
                'inherit_global_headers' => true,
                'long_term_execution'    => false,
            ]);

            foreach ($this->extractHeaders($request) as $header) {
                $this->headerRepo->create([
                    'integration_service_id' => $service->id,
                    'type'                   => 'normal',
                    'header_key'             => $header['key'],
                    'require_from'           => 'admin',
                    'value'                  => $header['value'] ?? '',
                    'label'                  => $header['key'],
                    'is_active'              => true,
                ]);
            }

            foreach ($this->extractBodyFields($request) as $key) {
                $this->inputRepo->create([
                    'integration_service_id' => $service->id,
                    'group_id'               => null,
                    'field_type'             => 'input',
                    'key'                    => $key,
                    'type'                   => 'input',
                    'key_type'               => 'body',
                    'validation'             => 'nullable',
                    'require_from'           => 'user',
                ]);
            }

            $created[] = $service;
        }

        return $created;
    }

    /**
     * Postman folders nest requests under `item[].item[]` — recursively flatten
     * to a single ordered list of request items, ignoring folder structure.
     */
    private function flatten(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            if (isset($item['item']) && is_array($item['item'])) {
                $result = array_merge($result, $this->flatten($item['item']));
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }

    private function extractEndpointPath(string|array $url): string
    {
        if (is_string($url)) {
            $path = parse_url($url, PHP_URL_PATH);

            return $path ? '/'.ltrim($path, '/') : '/';
        }

        if (isset($url['path'])) {
            $path = is_array($url['path']) ? implode('/', $url['path']) : $url['path'];

            return '/'.ltrim((string) $path, '/');
        }

        if (isset($url['raw'])) {
            return $this->extractEndpointPath($url['raw']);
        }

        return '/';
    }

    private function extractHeaders(array $request): array
    {
        return collect($request['header'] ?? [])
            ->filter(fn ($h) => empty($h['disabled']) && ! empty($h['key']))
            ->unique('key')
            ->values()
            ->all();
    }

    private function extractBodyFields(array $request): array
    {
        $body = $request['body'] ?? null;

        if (! $body) {
            return [];
        }

        $mode = $body['mode'] ?? null;

        if ($mode === 'raw') {
            $decoded = json_decode($body['raw'] ?? '', true);

            return is_array($decoded) ? array_keys($decoded) : [];
        }

        if (in_array($mode, ['urlencoded', 'formdata'], true)) {
            return collect($body[$mode] ?? [])
                ->filter(fn ($f) => empty($f['disabled']) && ! empty($f['key']))
                ->pluck('key')
                ->unique()
                ->values()
                ->all();
        }

        return [];
    }
}
