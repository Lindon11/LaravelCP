<?php

namespace App\Actions\Fortify;

use App\Plugins\Gang\Models\Team;
use App\Core\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateNewUser
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'username' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'level' => 1,
                'experience' => 0,
                'health' => 100,
                'max_health' => 100,
                'energy' => 100,
                'max_energy' => 100,
                'cash' => 10000,
                'bank' => 0,
                'respect' => 0,
                'bullets' => 50,
                'rank' => 'Thug',
                'rank_id' => 1, // Start at Thug rank
                'location' => 'Detroit',
                'location_id' => 1, // Start in Detroit
                'last_active' => now(),
            ]), function (User $user) {
                $this->createTeam($user);
            });
        });
    }

    /**
     * Create a personal team for the user.
     */
    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->create([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]);
    }
}
