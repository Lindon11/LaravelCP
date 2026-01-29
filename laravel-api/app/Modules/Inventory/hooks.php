<?php

use App\Facades\Hook;

// Item purchase event
Hook::register('OnItemBought', function ($data) {
    // Log item purchase
    activity()
        ->causedBy($data['player'])
        ->withProperties([
            'item' => $data['item']->name,
            'quantity' => $data['quantity'],
            'cost' => $data['cost']
        ])
        ->log('item_bought');
});

// Item sale event
Hook::register('OnItemSold', function ($data) {
    // Log item sale
    activity()
        ->causedBy($data['player'])
        ->withProperties([
            'item' => $data['item']->name,
            'quantity' => $data['quantity'],
            'earnings' => $data['earnings']
        ])
        ->log('item_sold');
});

// Item equipped event
Hook::register('OnItemEquipped', function ($data) {
    // Log equipment change
    activity()
        ->causedBy($data['player'])
        ->withProperties(['item' => $data['item']->name])
        ->log('item_equipped');
    
    // Update player stats cache
    cache()->forget('player_stats_' . $data['player']->id);
});

// Item unequipped event
Hook::register('OnItemUnequipped', function ($data) {
    // Log equipment change
    activity()
        ->causedBy($data['player'])
        ->withProperties(['item' => $data['item']->name])
        ->log('item_unequipped');
    
    // Update player stats cache
    cache()->forget('player_stats_' . $data['player']->id);
});

// Item used event
Hook::register('OnItemUsed', function ($data) {
    // Log item usage
    activity()
        ->causedBy($data['player'])
        ->withProperties(['item' => $data['item']->name])
        ->log('item_used');
});
