<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Drivers\Application\CreateDriver;
use App\Features\Drivers\Application\DeleteDriver;
use App\Features\Drivers\Application\UpdateDriver;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDriverRequest;
use App\Http\Requests\Admin\UpdateDriverRequest;
use App\Models\Driver;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class ServiceController extends Controller
{
    public function __construct(
    ) {}

    public function index(): View
    {
        $this->authorize(PermissionTypes::DRIVERS_VIEW);

        return view('dashboard.services.index');
        return view('drivers.index');
    }
}