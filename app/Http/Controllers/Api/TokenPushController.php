<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class TokenPushController extends Controller
{
    public function __construct(
    ) {}





    public function store(Request $request)
    {
        
    info($request->all());
        // $this->authorize(PermissionTypes::DRIVERS_VIEW);

        // return view('dashboard.services.index');
        // return view('drivers.index');
    }
}