<?php

$basePath = __DIR__ . '/..';
$modulesPath = $basePath . '/modules';

$modules = [
    ['bank', 'Bank', '🏦', 'Deposit and withdraw money safely', 4, ['cooldown' => 0, 'minLevel' => 1, 'energyCost' => 0, 'transferFee' => 5, 'maxDeposit' => 1000000]],
    ['travel', 'Travel', '✈️', 'Travel between cities', 5, ['cooldown' => 120, 'minLevel' => 1, 'energyCost' => 5, 'baseCost' => 100]],
    ['drugs', 'Drugs', '💊', 'Buy and sell drugs for profit', 6, ['cooldown' => 300, 'minLevel' => 5, 'energyCost' => 10, 'maxQuantity' => 100]],
    ['theft', 'Theft', '🥷', 'Steal from other players', 7, ['cooldown' => 180, 'minLevel' => 3, 'energyCost' => 15, 'baseReward' => 500]],
    ['racing', 'Racing', '🏎️', 'Race cars and win prizes', 8, ['cooldown' => 240, 'minLevel' => 5, 'energyCost' => 20, 'entryFee' => 1000, 'firstPrize' => 5000]],
    ['jail', 'Jail', '⛓️', 'Escape from jail or bust others out', 9, ['cooldown' => 600, 'minLevel' => 1, 'energyCost' => 25, 'escapeChance' => 0.3, 'bustCost' => 10000]],
    ['inventory', 'Inventory', '🎒', 'Manage items and equipment', 10, ['cooldown' => 0, 'minLevel' => 1, 'energyCost' => 0, 'maxSlots' => 50]],
    ['properties', 'Properties', '🏠', 'Buy and manage real estate', 11, ['cooldown' => 0, 'minLevel' => 3, 'energyCost' => 0, 'sellPenalty' => 0.3, 'maxProperties' => 10]],
    ['combat', 'Combat', '⚔️', 'Fight other players', 12, ['cooldown' => 30, 'minLevel' => 5, 'energyCost' => 10, 'bulletsCost' => 10, 'respawnTime' => 300]],
    ['bounty', 'Bounty', '💰', 'Place bounties on players', 13, ['cooldown' => 0, 'minLevel' => 10, 'energyCost' => 0, 'minBounty' => 10000, 'maxBounty' => 1000000]],
    ['detective', 'Detective', '🔍', 'Investigate other players', 14, ['cooldown' => 60, 'minLevel' => 7, 'energyCost' => 5, 'searchCost' => 5000]],
    ['bullets', 'Bullets', '🔫', 'Buy ammunition for combat', 15, ['cooldown' => 0, 'minLevel' => 1, 'energyCost' => 0, 'pricePerBullet' => 100, 'maxPurchase' => 1000]],
    ['gang', 'Gang', '👥', 'Join or create a gang', 16, ['cooldown' => 0, 'minLevel' => 10, 'energyCost' => 0, 'creationCost' => 100000, 'maxMembers' => 20]],
    ['missions', 'Missions', '📋', 'Complete missions for rewards', 17, ['cooldown' => 3600, 'minLevel' => 1, 'energyCost' => 0, 'dailyLimit' => 10]],
    ['achievements', 'Achievements', '🏆', 'Unlock achievements', 18, ['cooldown' => 0, 'minLevel' => 1, 'energyCost' => 0, 'totalAchievements' => 50]],
    ['leaderboards', 'Leaderboards', '📊', 'View top players', 19, ['cooldown' => 0, 'minLevel' => 1, 'energyCost' => 0, 'topPlayersCount' => 100]],
    ['forum', 'Forum', '💬', 'Community discussions', 20, ['cooldown' => 30, 'minLevel' => 1, 'energyCost' => 0, 'maxPostLength' => 5000]],
    ['organized-crime', 'Organized Crime', '🏴', 'Team up for big heists', 21, ['cooldown' => 7200, 'minLevel' => 15, 'energyCost' => 50, 'minPlayers' => 3, 'maxPlayers' => 10, 'baseReward' => 50000]],
];

foreach ($modules as list($id, $name, $icon, $description, $order, $settings)) {
    $moduleDir = $modulesPath . '/' . $id . '-module';
    $moduleFile = $moduleDir . '/module.json';
    
    if (!file_exists($modulDir)) {
        mkdir($moduleDir, 0755, true);
    }
    
    $data = [
        'id' => $id,
        'name' => $name,
        'icon' => $icon,
        'description' => $description,
        'enabled' => true,
        'order' => $order,
        'version' => '1.0.0',
        'settings' => $settings
    ];
    
    file_put_contents($moduleFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✓ Created $moduleFile\n";
}

echo "\nTotal modules created: " . count($modules) . "\n";
