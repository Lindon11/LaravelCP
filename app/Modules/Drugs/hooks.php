<?php

use App\Facades\Hook;

// Drug purchase event
Hook::register('OnDrugsBought', function ($data) {
    // Log drug purchase
    activity()
        ->causedBy($data['player'])
        ->withProperties([
            'drug' => $data['drug']->name,
            'quantity' => $data['quantity'],
            'cost' => $data['cost']
        ])
        ->log('drugs_bought');
});

// Drug sale event
Hook::register('OnDrugsSold', function ($data) {
    // Log drug sale
    activity()
        ->causedBy($data['player'])
        ->withProperties([
            'drug' => $data['drug']->name,
            'quantity' => $data['quantity'],
            'earnings' => $data['earnings']
        ])
        ->log('drugs_sold');
});

// Price update event
Hook::register('OnPriceUpdate', function ($data) {
    // Cache updated prices
    cache()->put('drug_prices_' . $data['location_id'], $data['prices'], now()->addMinutes(5));
});
