<?php

use App\Modules\Bullets\BulletsModule;

return [
    /**
     * Triggered when bullets are purchased
     */
    'OnBulletsPurchased' => function ($data) {
        // Log bullet purchase
        \Log::info('Bullets purchased', [
            'player' => $data['player']->username,
            'quantity' => $data['quantity'],
            'cost' => $data['cost'],
        ]);
        
        return $data;
    },
    
    /**
     * Alter bullet shop data before display
     */
    'alterModuleData' => function ($data) {
        // Can be used to modify pricing or add special offers
        return $data;
    },
];
