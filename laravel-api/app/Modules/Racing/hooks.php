<?php

use App\Facades\Hook;

// Race created event
Hook::register('OnRaceCreated', function ($data) {
    // Log race creation
    activity()
        ->causedBy($data['creator'])
        ->withProperties([
            'race_name' => $data['race']->name,
            'entry_fee' => $data['race']->entry_fee
        ])
        ->log('race_created');
});

// Player joined race event
Hook::register('OnRaceJoined', function ($data) {
    // Log race join
    activity()
        ->causedBy($data['player'])
        ->withProperties(['race_id' => $data['race']->id])
        ->log('race_joined');
});

// Race started event
Hook::register('OnRaceStarted', function ($data) {
    // Log race start
    activity()
        ->withProperties([
            'race_id' => $data['race']->id,
            'participants' => count($data['participants'])
        ])
        ->log('race_started');
});

// Race finished event
Hook::register('OnRaceFinished', function ($data) {
    // Log race completion and award winner
    activity()
        ->causedBy($data['winner'])
        ->withProperties([
            'race_id' => $data['race']->id,
            'prize' => $data['prize']
        ])
        ->log('race_won');
});
