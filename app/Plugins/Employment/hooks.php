<?php

use App\Plugins\Employment\EmploymentModule;
use Illuminate\Support\Facades\Log;

return [
    'OnJobApplied' => function ($data) {
        Log::info('Player applied for job', [
            'user_id' => $data['user']->id,
            'position' => $data['position']->name,
            'company' => $data['company']->name ?? null,
        ]);
        return $data;
    },

    'OnWorkCompleted' => function ($data) {
        Log::info('Player completed work shift', [
            'user_id' => $data['user']->id,
            'earnings' => $data['earnings'],
            'exp' => $data['exp'],
        ]);
        return $data;
    },

    'OnJobQuit' => function ($data) {
        Log::info('Player quit job', [
            'user_id' => $data['user']->id,
            'position' => $data['position'] ?? null,
        ]);
        return $data;
    },

    'alterEmploymentCompanies' => function ($data) {
        return $data;
    },
];
