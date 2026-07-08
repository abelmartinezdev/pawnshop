<?php

namespace App\Http\Controllers\Offices;

use App\Actions\Offices\CreateOfficeAction;
use App\Actions\Offices\DeleteOfficeAction;
use App\Actions\Offices\EditOfficeAction;
use App\Actions\Offices\IndexOfficeAction;
use App\Actions\Offices\RestoreOfficeAction;
use App\Actions\Offices\ShowOfficeAction;
use App\Actions\Offices\StoreOfficeAction;
use App\Actions\Offices\UpdateOfficeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Offices\StoreOfficeRequest;
use App\Http\Requests\Offices\UpdateOfficeRequest;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class OfficeController extends Controller
{
    public function index(Request $request, IndexOfficeAction $action): Response
    {
        return $action($request);
    }

    public function create(Request $request, CreateOfficeAction $action): Response
    {
        return $action($request);
    }

    public function store(StoreOfficeRequest $request, StoreOfficeAction $action): RedirectResponse
    {
        return $action($request);
    }

    public function show(Office $office, ShowOfficeAction $action): Response
    {
        return $action($office);
    }

    public function edit(Office $office, EditOfficeAction $action): Response
    {
        return $action($office);
    }

    public function update(
        UpdateOfficeRequest $request,
        Office $office,
        UpdateOfficeAction $action
    ): RedirectResponse {
        return $action($request, $office);
    }

    public function destroy(Office $office, DeleteOfficeAction $action): RedirectResponse
    {
        return $action($office);
    }

    public function restore(int $id, RestoreOfficeAction $action): RedirectResponse
    {
        return $action($id);
    }
}