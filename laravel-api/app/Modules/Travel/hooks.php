<?php

use App\Facades\Hook;

// Player travel event
Hook::register('OnPlayerTravel', function ($data) {
    // Log travel
    activity()
        ->causedBy($data['player'])
        ->withProperties([
            'from' => $data['from']->name,
            'to' => $data['to']->name,
            'cost' => $data['cost']
        ])
        ->log('player_traveled');
});

// Location enter event
Hook::register('OnLocationEnter', function ($data) {
    // Update location-specific state
    cache()->increment('location_' . $data['location']->id . '_players');
    
    // Trigger location-specific events
    event(new \App\Events\PlayerEnteredLocation($data['player'], $data['location']));
});

// Location leave event
Hook::register('OnLocationLeave', function ($data) {
    // Update location-specific state
    cache()->decrement('location_' . $data['location']->id . '_players');
});
