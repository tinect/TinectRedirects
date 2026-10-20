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
     * Removes the given parameters from a raw query string, keeping order and encoding of all other parameters.
     * Returns the remaining and the removed query string.
     *
     * @param array<string> $parameters
     *
     * @return array{string, string}
     */
    public static function removeParameters(string $queryString, array $parameters): array
    {
        $kept = [];
        $removed = [];

        foreach (\explode('&', $queryString) as $pair) {
            if ($pair === '') {
                continue;
            }

            $key = \urldecode(\explode('=', $pair, 2)[0]);

            // Array parameters like "utm_source[]=foo" are matched by their name
            $bracketPosition = \strpos($key, '[');
            if ($bracketPosition !== false) {
                $key = \substr($key, 0, $bracketPosition);
            }

            if (\in_array($key, $parameters, true)) {
                $removed[] = $pair;
            } else {
                $kept[] = $pair;
            }
        }

        return [\implode('&', $kept), \implode('&', $removed)];
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
