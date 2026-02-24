<?php

namespace Grebennik\IpClock;

use DateTimeImmutable;

/**
 * Interface ResponseParserInterface
 */
interface ResponseParserInterface
{
    /**
     * Parses the API response data and returns a DateTimeImmutable object.
     *
     * @param array $data The decoded JSON response from the API.
     * @return DateTimeImmutable
     * @throws \RuntimeException If the response data is invalid.
     */
    public function parse(array $data): DateTimeImmutable;
}
