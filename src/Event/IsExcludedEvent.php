<?php

namespace Tinect\Redirects\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tinect\Redirects\Message\TinectRedirectUpdateMessage;

class IsExcludedEvent extends Event
{
    private bool $isExcluded = false;

    public function __construct(
        private readonly TinectRedirectUpdateMessage $redirectUpdateMessage
    ) {
    }

    public function isExcluded(): bool
    {
        return $this->isExcluded;
    }

    public function setIsExcluded(bool $isExcluded): void
    {
        $this->isExcluded = $isExcluded;
    }

    public function getUpdateMessage(): TinectRedirectUpdateMessage
    {
        return $this->redirectUpdateMessage;
    }
}
