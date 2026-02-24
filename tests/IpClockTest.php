<?php

namespace Grebennik\IpClock\Tests;

use PHPUnit\Framework\TestCase;
use Grebennik\IpClock\IpClock;
use Grebennik\IpClock\ResponseParserInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Clock\ClockInterface;
use DateTimeImmutable;
use RuntimeException;

class IpClockTest extends TestCase
{
    public function testImplementsClockInterface()
    {
        $clock = new IpClock();
        $this->assertInstanceOf(ClockInterface::class, $clock);
    }

    public function testNowReturnsDateTimeImmutableFromApi()
    {
        $mockBody = json_encode([
            'datetime' => '2023-10-27T10:00:00Z',
            'timezone' => 'UTC'
        ]);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('request')
            ->with('GET', 'https://worldtimeapi.org/api/ip')
            ->willReturn(new Response(200, [], $mockBody));

        $clock = new IpClock(null, $httpClient);
        $now = $clock->now();

        $this->assertInstanceOf(DateTimeImmutable::class, $now);
        $this->assertEquals('2023-10-27T10:00:00+00:00', $now->format('c'));
        $this->assertStringContainsString('UTC', $now->getTimezone()->getName() === 'Z' ? 'UTC' : $now->getTimezone()->getName());
    }

    public function testNowUsesCustomParser()
    {
        $mockBody = json_encode(['foo' => 'bar']);
        $expectedTime = new DateTimeImmutable('2024-01-01T12:00:00+01:00');

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('request')
            ->willReturn(new Response(200, [], $mockBody));

        $parser = $this->createMock(ResponseParserInterface::class);
        $parser->expects($this->once())
            ->method('parse')
            ->with(['foo' => 'bar'])
            ->willReturn($expectedTime);

        $clock = new IpClock(null, $httpClient, null, $parser);
        $now = $clock->now();

        $this->assertSame($expectedTime, $now);
    }

    public function testNowUsesProvidedIp()
    {
        $ip = '8.8.8.8';
        $mockBody = json_encode([
            'datetime' => '2023-10-27T10:00:00Z',
            'timezone' => 'America/New_York'
        ]);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('request')
            ->with('GET', 'https://worldtimeapi.org/api/ip/8.8.8.8')
            ->willReturn(new Response(200, [], $mockBody));

        $clock = new IpClock($ip, $httpClient);
        $now = $clock->now();

        $this->assertEquals('America/New_York', $now->getTimezone()->getName());
        $this->assertEquals($ip, $clock->getIp());
    }

    public function testNowUsesUrlPlaceholder()
    {
        $ip = '1.2.3.4';
        $apiUrl = 'https://api.test/v1/time?ip={ip}&key=secret';
        $expectedUrl = 'https://api.test/v1/time?ip=1.2.3.4&key=secret';
        
        $mockBody = json_encode([
            'datetime' => '2023-10-27T10:00:00Z',
            'timezone' => 'UTC'
        ]);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('request')
            ->with('GET', $expectedUrl)
            ->willReturn(new Response(200, [], $mockBody));

        $clock = new IpClock($ip, $httpClient, $apiUrl);
        $clock->now();
        
        $this->assertTrue(true); // Verification via $httpClient->method('request')->with(...)
    }

    public function testNowRemovesPlaceholderWhenNoIpProvided()
    {
        $apiUrl = 'https://api.test/v1/ip/{ip}';
        $expectedUrl = 'https://api.test/v1/ip';
        
        $mockBody = json_encode([
            'datetime' => '2023-10-27T10:00:00Z',
            'timezone' => 'UTC'
        ]);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('request')
            ->with('GET', $expectedUrl)
            ->willReturn(new Response(200, [], $mockBody));

        $clock = new IpClock(null, $httpClient, $apiUrl);
        $clock->now();
        
        $this->assertTrue(true);
    }

    public function testThrowsExceptionOnApiError()
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('request')
            ->willThrowException(new RuntimeException('Network error'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to retrieve time from API: Network error');

        $clock = new IpClock(null, $httpClient);
        $clock->now();
    }

    public function testThrowsExceptionOnInvalidApiResponse()
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('request')
            ->willReturn(new Response(200, [], json_encode(['foo' => 'bar'])));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to retrieve time from API: Invalid response from Time API');

        $clock = new IpClock(null, $httpClient);
        $clock->now();
    }
}
