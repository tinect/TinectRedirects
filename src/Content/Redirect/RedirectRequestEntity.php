<?php

declare(strict_types=1);

namespace Tinect\Redirects\Content\Redirect;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class RedirectRequestEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $redirectId = null;

    protected ?RedirectEntity $redirect = null;

    protected string $ipAddress = '';

    protected string $userAgent = '';
    protected string $referer   = '';

    public function getRedirectId(): ?string
    {
        return $this->redirectId;
    }

    public function setRedirectId(?string $redirectId): void
    {
        $this->redirectId = $redirectId;
    }

    public function getRedirect(): ?RedirectEntity
    {
        return $this->redirect;
    }

    public function setRedirect(?RedirectEntity $redirect): void
    {
        $this->redirect = $redirect;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    public function getReferer(): string
    {
        return $this->referer;
    }

    public function setReferer(string $referer): void
    {
        $this->referer = $referer;
    }
}
