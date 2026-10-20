<?php

declare(strict_types=1);

namespace Tinect\Redirects\Services;

use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\Psr7\Uri;

final class QueryString
{
    /**
     * Splits a request URI like "/foo?bar=1" into its path and query string.
     * The query string is null when the URI does not contain a "?".
     *
     * @return array{string, string|null}
     */
    public static function split(string $uri): array
    {
        $parts = \explode('?', $uri, 2);

        return [$parts[0], $parts[1] ?? null];
    }

    /**
     * Appends a raw query string to an URL, keeping its existing query and fragment.
     */
    public static function append(string $url, string $queryString): string
    {
        if ($queryString === '') {
            return $url;
        }

        try {
            $uri = new Uri($url);
        } catch (MalformedUriException) {
            return $url;
        }

        $existingQueryString = \rtrim($uri->getQuery(), '&');

        if ($existingQueryString !== '') {
            $queryString = $existingQueryString . '&' . $queryString;
        }

        return (string) $uri->withQuery($queryString);
    }
}
