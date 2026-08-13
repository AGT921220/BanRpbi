<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Manifest;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    public function index()
    {
        return view('dashboard.manifests.index');
    }
    public function show(int $manifestId)
    {
        return response()->json(
            ['data' => $this->getManifest($manifestId)],
            200
        );
    }
    private function getManifest(int $manifestId): array
    {
        $manifest = Manifest::with(['service' => function ($q) {
            $q->select(['id', 'client_id']);
            $q->with(['client' => function ($q) {
                $q->select([
                    'id',
                    'company as name',
                    'nra',
                    'postal_code',
                    'street',
                    'num_ext',
                    'num_int',
                    'colony',
                    'state',
                    'city'
                ]);
            }]);
        }])
            ->findOrFail($manifestId);
        return [
            'folio' => $manifest->id,
            'client' => $manifest->service->client,
            'driver' => 'TEST',
            'test' => config('business'),
            'transportista' => 'TEST',
        ];
    }
}
