<?php

namespace App\Http\Controllers;

use App\Models\Dealer;

class DealerController extends Controller
{
    public function index()
    {
        return view('dealers.index', [
            'dealers' => Dealer::withCount('orders')->orderBy('name')->get(),
        ]);
    }

    public function show(Dealer $dealer)
    {
        $dealer->load(['orders' => fn ($q) => $q->withCount('items')->latest('placed_on')->latest('id')]);

        return view('dealers.show', ['dealer' => $dealer]);
    }
}
