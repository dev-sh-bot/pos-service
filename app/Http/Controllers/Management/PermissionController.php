<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:permissions.view')->only(['index']);
    }

    public function index(): View
    {
        return view('permissions.index', [
            'permissions' => Permission::orderBy('group_name')->orderBy('name')->get(),
            'groups' => config('permissions'),
        ]);
    }
}
