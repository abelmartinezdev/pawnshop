<?php

namespace App\Actions\Closures;

use App\Modules\Closures\Closure;
use Illuminate\Http\Request;

class StoreClosureAction
{
    public function __invoke(Request $request)
    {
        \DB::beginTransaction();

        $office = \Pawnshop::office();
        $last_closure = Closure::where('office_id', $office->id)->latest('created_at')->first();

        if ($last_closure && $last_closure->created_at->isToday()) {
            \Pawnshop::openBox($office, $last_closure);
            \DB::commit();
            return redirect()->route('home')->with('success', '¡Caja abierta!');
        }

        $closing_pending = false;
        if ($last_closure && !$last_closure->created_at->isYesterday() && !$last_closure->created_at->isToday()) {
            $closing_pending = true;
        }

        if (!$closing_pending) {
            $closure = \Pawnshop::closeBox($office, auth()->user());
            \DB::commit();
            return redirect()->route('closure.show', $closure->id)->with('success', '¡Cierre de caja realizado con éxito!');
        }

        \Pawnshop::simulateCloseBox($office);
        \DB::commit();
        return redirect()->route('closure.create')->with('success', '¡Cierres de caja realizados con éxito!');
    }
}