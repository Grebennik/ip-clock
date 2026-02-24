# IP Clock (PSR-20)

A Composer package that returns accurate time and timezone based on a server’s external IP address. Implements `\Psr\Clock\ClockInterface` (PSR-20).

## Requirements

- PHP 8.2 or higher
- Composer

## Installation

Install the package via Composer:

```bash
composer require mhrebinnyk/ip-clock
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
use Mhrebinnyk\IpClock\IpClock;

$clock = new IpClock();
$now = $clock->now(); // Returns DateTimeImmutable

echo $now->format('Y-m-d H:i:s P');
```

### Use a specific IP address

```php
use Mhrebinnyk\IpClock\IpClock;

$clock = new IpClock('8.8.8.8');
$now = $clock->now();

echo "Time for 8.8.8.8: " . $now->format('H:i:s');
```

## Data source

By default, the package uses [WorldTimeAPI](http://worldtimeapi.org/). This service is free and does not require an API key.

### Custom API and Response Parsing

If you use a different API that returns data in a different format, you can implement `ResponseParserInterface` and pass it to the constructor:

```php
use Mhrebinnyk\IpClock\IpClock;
use Mhrebinnyk\IpClock\ResponseParserInterface;
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
