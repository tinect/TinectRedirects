<?php

declare(strict_types=1);

namespace Tinect\Redirects\Tests\Message;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tinect\Redirects\Content\Redirect\RedirectRequestDefinition;
use Tinect\Redirects\Message\TinectRedirectUpdateMessage;

#[CoversClass(TinectRedirectUpdateMessage::class)]
class TinectRedirectUpdateMessageTest extends TestCase
{
    public function testTruncationKeepsMultibyteCharactersIntact(): void
    {
        $message = new TinectRedirectUpdateMessage(
            source: '/foo',
            salesChannelDomainId: null,
            ipAddress: '',
            userAgent: 'a' . \str_repeat('ä', RedirectRequestDefinition::MAX_LENGTH_USER_AGENT),
            createRedirect: false,
            id: null,
            referer: 'a' . \str_repeat('ä', RedirectRequestDefinition::MAX_LENGTH_REFERER)
        );

        $userAgent = $message->getUserAgent();
        $referer = (string) $message->getReferer();

        static::assertTrue(\mb_check_encoding($userAgent, 'UTF-8'));
        static::assertSame(RedirectRequestDefinition::MAX_LENGTH_USER_AGENT, \mb_strlen($userAgent));
        static::assertTrue(\mb_check_encoding($referer, 'UTF-8'));
        static::assertSame(RedirectRequestDefinition::MAX_LENGTH_REFERER, \mb_strlen($referer));
    }
}
