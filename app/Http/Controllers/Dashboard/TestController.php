<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        // return 'INDEX';
        $locations = [
            [
                'latitude' => 25.6866,
                'longitude' => -100.3161,
            ],
            [
                'latitude' => 25.7001,
                'longitude' => -100.3020,
            ],
            [
                'latitude' => 25.7205,
                'longitude' => -100.2800,
            ],
        ];
        return view('test', compact('locations'));
    }
}
