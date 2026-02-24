<?php

namespace Mhrebinnyk\IpClock;

use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

/**
 * Class DefaultResponseParser
 *
 * Default parser for WorldTimeAPI.org response.
 */
class DefaultResponseParser implements ResponseParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(array $data): DateTimeImmutable
    {
        if (!isset($data['datetime']) || !isset($data['timezone'])) {
            throw new RuntimeException('Invalid response from Time API: Missing datetime or timezone field.');
        }

        try {
            $timezone = new DateTimeZone($data['timezone']);
            $datetime = new DateTimeImmutable($data['datetime']);
            
            return $datetime->setTimezone($timezone);
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to parse date/time from API: ' . $e->getMessage(), 0, $e);
        }
    }
}
