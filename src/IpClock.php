<?php

namespace Grebennik\IpClock;

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
    private ResponseParserInterface $parser;

    /**
     * @param string|null $ip The IP address to get the time for. If null, the server's external IP is used.
     * @param ClientInterface|null $httpClient Custom HTTP client.
     * @param string|null $apiUrl Custom API URL.
     * @param ResponseParserInterface|null $parser Custom API response parser.
     */
    public function __construct(
        ?string $ip = null,
        ?ClientInterface $httpClient = null,
        ?string $apiUrl = null,
        ?ResponseParserInterface $parser = null
    ) {
        $this->ip = $ip;
        $this->httpClient = $httpClient ?? new Client(['timeout' => 5.0]);
        $this->apiUrl = $apiUrl ?? self::DEFAULT_API_URL;
        $this->parser = $parser ?? new DefaultResponseParser();
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
                if (str_contains($url, '{ip}')) {
                    $url = str_replace('{ip}', urlencode($this->ip), $url);
                } else {
                    $url = rtrim($url, '/') . '/' . $this->ip;
                }
            } else {
                // If ip is not provided, we remove the placeholder if it exists (for some APIs like worldtimeapi/ip)
                $url = str_replace('{ip}', '', $url);
                $url = rtrim($url, '/');
            }

            $response = $this->httpClient->request('GET', $url);
            $data = json_decode($response->getBody()->getContents(), true);

            return $this->parser->parse($data);
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
