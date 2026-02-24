<?php

namespace Mhrebinnyk\IpClock;

use Psr\Clock\ClockInterface;
use DateTimeImmutable;
use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use RuntimeException;

/**
 * Class IpClock
 */
class IpClock implements ClockInterface
{
    private const DEFAULT_API_URL = 'https://worldtimeapi.org/api/ip';
    private ?string $ip;
    private ClientInterface $httpClient;
    private ?string $apiUrl;

    /**
     * @param string|null $ip The IP address to get the time for. If null, the server's external IP is used.
     * @param ClientInterface|null $httpClient Custom HTTP client.
     * @param string|null $apiUrl Custom API URL (should follow worldtimeapi.org format).
     */
    public function __construct(
        ?string $ip = null,
        ?ClientInterface $httpClient = null,
        ?string $apiUrl = null
    ) {
        $this->ip = $ip;
        $this->httpClient = $httpClient ?? new Client(['timeout' => 5.0]);
        $this->apiUrl = $apiUrl ?? self::DEFAULT_API_URL;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException If the time could not be retrieved from the API.
     */
    public function now(): DateTimeImmutable
    {
        try {
            $url = $this->apiUrl;
            if ($this->ip) {
                $url = rtrim($url, '/') . '/' . $this->ip;
            }

            $response = $this->httpClient->request('GET', $url);
            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['datetime']) || !isset($data['timezone'])) {
                throw new RuntimeException('Invalid response from Time API');
            }

            $timezone = new DateTimeZone($data['timezone']);
            $datetime = new DateTimeImmutable($data['datetime']);
            
            return $datetime->setTimezone($timezone);
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to retrieve time from API: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get the IP address being used.
     *
     * @return string|null
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }
}
