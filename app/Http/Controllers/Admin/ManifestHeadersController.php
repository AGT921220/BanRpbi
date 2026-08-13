<?php

namespace App\Http\Controllers\Admin;

use App\Features\Manifests\Application\SearchManifestHeaders;
use App\Features\Shared\Query\QueryFilter;
use App\Features\Shared\Query\QueryOptions;
use App\Http\Controllers\Controller;
use App\Models\Manifest;
use Illuminate\Http\Request;

class ManifestHeadersController extends Controller
{

    public function __construct(private readonly SearchManifestHeaders $searchManifestHeaders)
    {
    }


    public function index()
    {

    $filters = [
        // QueryFilter::WHERE(field: "id", value: 1),
        // QueryOptions::orderBy(field: "id", direction: "desc"),
        QueryOptions::limit(limit: 10),
        QueryOptions::offset(offset: 0),
    ];
    return ($this->searchManifestHeaders)(
        $filters
    );
    return Manifest::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
