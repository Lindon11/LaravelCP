<?php

use App\Facades\Hook;

Hook::register('OnAllianceCreated', function ($data) {
    return $data;
});

Hook::register('OnAllianceJoin', function ($data) {
    return $data;
});

Hook::register('OnAllianceWar', function ($data) {
    return $data;
});

Hook::register('OnTerritoryCapture', function ($data) {
    return $data;
});
