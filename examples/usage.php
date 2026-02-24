<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Grebennik\IpClock\IpClock;
use Grebennik\IpClock\ResponseParserInterface;
use GuzzleHttp\Client;

// Example 1: Use server's external IP automatically (WorldTimeAPI)
echo "Example 1: Using server's external IP (Default WorldTimeAPI)\n";
$clock = new IpClock();
try {
    $now = $clock->now();
    echo "Current time (Server IP): " . $now->format('Y-m-d H:i:s P') . "\n";
    echo "Timezone: " . $now->getTimezone()->getName() . "\n";
} catch (Exception $e) {
    echo "Error (WorldTimeAPI): " . $e->getMessage() . "\n";
}

echo "-----------------------------------\n";

// Example 2: Use a specific IP (e.g., Google DNS 8.8.8.8)
$specificIp = '8.8.8.8';
echo "Example 2: Using specific IP ($specificIp)\n";
$clockWithIp = new IpClock($specificIp);
try {
    $now = $clockWithIp->now();
    echo "Current time (IP {$specificIp}): " . $now->format('Y-m-d H:i:s P') . "\n";
    echo "Timezone: " . $now->getTimezone()->getName() . "\n";
} catch (Exception $e) {
    echo "Error (WorldTimeAPI): " . $e->getMessage() . "\n";
}

echo "-----------------------------------\n";

// Example 3: Using an alternative API with a custom parser (TimeAPI.io)
echo "Example 3: Using TimeAPI.io with Custom Parser\n";

class TimeApiIoParser implements ResponseParserInterface
{
    public function parse(array $data): DateTimeImmutable
    {
        if (!isset($data['dateTime'], $data['timeZone'])) {
            throw new RuntimeException('Invalid response from TimeAPI.io');
        }
        $dt = new DateTimeImmutable($data['dateTime']);
        return $dt->setTimezone(new DateTimeZone($data['timeZone']));
    }
}

try {
    // Note: We use 'verify' => false here only for demonstration if your environment has SSL issues.
    // In production, you should have correct certificates.
    $httpClient = new Client(['timeout' => 10.0, 'verify' => false]);
    $clockCustom = new IpClock(
        ip: null,
        httpClient: $httpClient,
        apiUrl: 'https://www.timeapi.io/api/Time/current/zone?timeZone=UTC',
        parser: new TimeApiIoParser()
    );
    $now = $clockCustom->now();
    echo "Current time (TimeAPI.io): " . $now->format('Y-m-d H:i:s P') . "\n";
} catch (Exception $e) {
    echo "Error (TimeAPI.io): " . $e->getMessage() . "\n";
}
