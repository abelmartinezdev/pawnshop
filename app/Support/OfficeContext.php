<?php

namespace App\Support;

use Illuminate\Http\Request;

class OfficeContext
{
    public function __construct(private Request $request) {}

    public function id(): int
    {
        return (int) $this->request->attributes->get('office_id');
    }
}