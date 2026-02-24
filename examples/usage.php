<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Mhrebinnyk\IpClock\IpClock;

// Example 1: Use server's external IP automatically
$clock = new IpClock();
try {
    $now = $clock->now();
    echo "Current time (Server IP): " . $now->format('Y-m-d H:i:s P') . "\n";
    echo "Timezone: " . $now->getTimezone()->getName() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "-----------------------------------\n";

// Example 2: Use a specific IP (e.g., Google DNS 8.8.8.8)
$specificIp = '8.8.8.8';
$clockWithIp = new IpClock($specificIp);
try {
    $now = $clockWithIp->now();
    echo "Current time (IP {$specificIp}): " . $now->format('Y-m-d H:i:s P') . "\n";
    echo "Timezone: " . $now->getTimezone()->getName() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
