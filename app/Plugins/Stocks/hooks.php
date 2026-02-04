<?php

/**
 * Stocks Module Hooks
 */

return [
    'OnStockBuy' => function($data) {
        // Could trigger achievements, notifications
        return $data;
    },
    
    'OnStockSell' => function($data) {
        return $data;
    },
    
    'OnPriceUpdate' => function($data) {
        // Could trigger notifications for big price swings
        return $data;
    },
    
    // Modify transaction fees (membership discounts, etc.)
    'alterTransactionFees' => function($data) {
        return $data;
    },
    
    'moduleLoad' => function() {
        // Initialization
    },
];
