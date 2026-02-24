# IP Clock (PSR-20)

A Composer package that returns accurate time and timezone based on a server’s external IP address. Implements `\Psr\Clock\ClockInterface` (PSR-20).

## Requirements

- PHP 8.2 or higher
- Composer

## Installation

Install the package via Composer:

```bash
composer require grebennik/ip-clock
```

*Note: If the package is not yet published on Packagist, add a VCS repository to your `composer.json`:*

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/your-username/ip-clock"
        }
    ]
}
```

## Usage

### Automatically use the server’s external IP

```php
use Grebennik\IpClock\IpClock;

$clock = new IpClock();
$now = $clock->now(); // Returns DateTimeImmutable

echo $now->format('Y-m-d H:i:s P');
```

### Use a specific IP address

```php
use Grebennik\IpClock\IpClock;

$clock = new IpClock('8.8.8.8');
$now = $clock->now();

echo "Time for 8.8.8.8: " . $now->format('H:i:s');
```

## Data source

By default, the package uses [WorldTimeAPI](http://worldtimeapi.org/). This service is free and does not require an API key.

### Using Authenticated APIs (API Keys)

Many commercial time/IP services (like IpStack, AbstractAPI, etc.) require an API key. You can provide it in two ways depending on the API requirements:

#### Option 1: Via Headers (Recommended)

If the service expects the key in a header (e.g., `X-API-Key` or `Authorization`), pass a pre-configured Guzzle client:

```php
use Grebennik\IpClock\IpClock;
use GuzzleHttp\Client;

$client = new Client([
    'headers' => [
        'X-API-Key' => 'your_secret_key_here'
    ]
]);

$clock = new IpClock(httpClient: $client, apiUrl: 'https://api.example.com/time');
```

#### Option 2: Via Query Parameters

If the key must be in the URL, use the `{ip}` placeholder to define where the IP address should be inserted:

```php
use Grebennik\IpClock\IpClock;

// The {ip} placeholder will be replaced with the actual IP address
$apiUrl = 'https://api.example.com/time?key=your_secret_key&ip={ip}';

$clock = new IpClock(
    ip: '8.8.8.8',
    apiUrl: $apiUrl,
    parser: new MyCustomParser()
);
```

### Custom API and Response Parsing

If you use a different API that returns data in a different format, you can implement `ResponseParserInterface` and pass it to the constructor:

```php
use Grebennik\IpClock\IpClock;
use Grebennik\IpClock\ResponseParserInterface;
use DateTimeImmutable;

class MyCustomParser implements ResponseParserInterface 
{
    public function parse(array $data): DateTimeImmutable 
    {
        // Your logic to extract time from $data
        return new DateTimeImmutable($data['custom_time_field']);
    }
}

$clock = new IpClock(
    apiUrl: 'https://my-custom-api.com/time',
    parser: new MyCustomParser()
);
```

You can also inject your own HTTP client (Guzzle or any `Psr\Http\Client\ClientInterface`) via the constructor.

## Testing

Run tests with:

```bash
vendor/bin/phpunit tests
```

## License

MIT
