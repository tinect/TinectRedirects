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

    /**
     * @param array{string, string} $expected
     */
    #[DataProvider('removeParametersProvider')]
    public function testRemoveParameters(string $queryString, array $expected): void
    {
        static::assertSame($expected, QueryString::removeParameters($queryString, ['gclid', 'utm_source']));
    }

    /**
     * @return iterable<string, array{string, array{string, string}}>
     */
    public static function removeParametersProvider(): iterable
    {
        yield 'empty query string' => ['', ['', '']];
        yield 'no ignored parameters' => ['a=1&b=2', ['a=1&b=2', '']];
        yield 'only ignored parameters' => ['gclid=123&utm_source=news', ['', 'gclid=123&utm_source=news']];
        yield 'keeps order of mixed parameters' => ['b=2&gclid=123&a=1', ['b=2&a=1', 'gclid=123']];
        yield 'keeps encoding' => ['q=a%20b&gclid=1%2B2', ['q=a%20b', 'gclid=1%2B2']];
        yield 'encoded key' => ['utm%5Fsource=news&a=1', ['a=1', 'utm%5Fsource=news']];
        yield 'array parameter' => ['utm_source[]=news&a[]=1', ['a[]=1', 'utm_source[]=news']];
        yield 'parameter without value' => ['gclid&a', ['a', 'gclid']];
        yield 'empty pairs are dropped' => ['&a=1&&gclid=123&', ['a=1', 'gclid=123']];
        yield 'similar names are kept' => ['gclid_x=1&xgclid=2', ['gclid_x=1&xgclid=2', '']];
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
