<?php

/**
 * Casino Module Hooks
 */

return [
    'OnBetPlaced' => function($data) {
        return $data;
    },
    
    'OnBetWon' => function($data) {
        // Could trigger achievements, etc.
        return $data;
    },
    
    'OnBetLost' => function($data) {
        return $data;
    },
    
    'OnLotteryTicketPurchased' => function($data) {
        return $data;
    },
    
    // Modify winnings (membership bonuses, etc.)
    'alterWinnings' => function($data) {
        return $data;
    },
    
    'moduleLoad' => function() {
        // Initialization
    },
];
