<?php

use App\Facades\Hook;

Hook::register('OnListingCreated', function ($data) {
    return $data;
});

Hook::register('OnItemSold', function ($data) {
    return $data;
});

Hook::register('OnAuctionEnd', function ($data) {
    return $data;
});
