<?php

declare(strict_types=1);

namespace Tinect\Redirects\Tests\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tinect\Redirects\Services\QueryString;

#[CoversClass(QueryString::class)]
class QueryStringTest extends TestCase
{
    /**
     * @param array{string, string|null} $expected
     */
    #[DataProvider('splitProvider')]
    public function testSplit(string $uri, array $expected): void
    {
        static::assertSame($expected, QueryString::split($uri));
    }

    /**
     * @return iterable<string, array{string, array{string, string|null}}>
     */
    public static function splitProvider(): iterable
    {
        yield 'without query string' => ['/foo', ['/foo', null]];
        yield 'with query string' => ['/foo?gclid=123', ['/foo', 'gclid=123']];
        yield 'with empty query string' => ['/foo?', ['/foo', '']];
        yield 'with question mark in query string' => ['/foo?a=1?b=2', ['/foo', 'a=1?b=2']];
    }

    #[DataProvider('appendProvider')]
    public function testAppend(string $url, string $queryString, string $expected): void
    {
        static::assertSame($expected, QueryString::append($url, $queryString));
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function appendProvider(): iterable
    {
        yield 'empty query string' => ['https://example.com/bar', '', 'https://example.com/bar'];
        yield 'without existing query' => ['https://example.com/bar', 'gclid=123', 'https://example.com/bar?gclid=123'];
        yield 'with existing query' => ['https://example.com/bar?a=1', 'gclid=123', 'https://example.com/bar?a=1&gclid=123'];
        yield 'with trailing question mark' => ['https://example.com/bar?', 'gclid=123', 'https://example.com/bar?gclid=123'];
        yield 'with trailing ampersand' => ['https://example.com/bar?a=1&', 'gclid=123', 'https://example.com/bar?a=1&gclid=123'];
        yield 'with fragment' => ['https://example.com/bar#top', 'gclid=123', 'https://example.com/bar?gclid=123#top'];
        yield 'with existing query and fragment' => ['https://example.com/bar?a=1#top', 'b=2', 'https://example.com/bar?a=1&b=2#top'];
        yield 'keeps encoded values' => ['https://example.com/bar', 'q=a%20b&c%5B%5D=1', 'https://example.com/bar?q=a%20b&c%5B%5D=1'];
        yield 'malformed url stays untouched' => ['http:///bar', 'gclid=123', 'http:///bar'];
    }
}
