<?php

namespace App\Core\Observers;

use App\Core\Models\User;

class PlayerObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Auto-create player profile for new users
        User::create([
            'user_id' => $user->id,
            'username' => $user->name,
            'level' => 1,
            'health' => 100,
            'max_health' => 100,
            'cash' => 1000,
            'bank' => 0,
            'respect' => 0,
            'bullets' => 10,
            'rank' => 'Thug'
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Player will be deleted via cascade
    }
}
