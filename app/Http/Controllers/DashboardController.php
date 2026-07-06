<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\ShowDashboardAction;
use Illuminate\Http\Request;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, ShowDashboardAction $action): Response
    {
        return $action($request);
    }
}