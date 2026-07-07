<?php

namespace App\Http\Controllers;

use App\Actions\Products\CreateProductAction;
use App\Actions\Products\DestroyProductAction;
use App\Actions\Products\EditProductAction;
use App\Actions\Products\IndexProductAction;
use App\Actions\Products\RestoreProductAction;
use App\Actions\Products\ShowProductAction;
use App\Actions\Products\StoreProductAction;
use App\Actions\Products\UpdateProductAction;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ProductsController extends Controller
{
    public function index(Request $request, IndexProductAction $action): Response
    {
        return $action($request);
    }

    public function create(CreateProductAction $action): Response
    {
        return $action();
    }

    public function store(StoreProductRequest $request, StoreProductAction $action): RedirectResponse
    {
        return $action($request);
    }

    public function show(int $product, ShowProductAction $action): Response
    {
        return $action($product);
    }

    public function edit(int $product, EditProductAction $action): Response
    {
        return $action($product);
    }

    public function update(
        int $product,
        UpdateProductRequest $request,
        UpdateProductAction $action
    ): RedirectResponse {
        return $action($product, $request);
    }

    public function destroy(int $product, DestroyProductAction $action): RedirectResponse
    {
        return $action($product);
    }

    public function restore(int $id, RestoreProductAction $action): RedirectResponse
    {
        return $action($id);
    }
}