<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Repository\CookieConsentRepository;
use App\State\Processor\CookieConsentProcessor;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ApiResource(operations: [
    new Post(
        uriTemplate: '/cookie_consent',
        normalizationContext: ['groups' => ['cookie_consent:read']],
        denormalizationContext: ['groups' => ['cookie_consent:write']],
        processor: CookieConsentProcessor::class,
    ),
])]
#[ORM\Entity(repositoryClass: CookieConsentRepository::class)]
class CookieConsent
{
    public const RESOURCE_KEY = 'cookieConsents';
    public const FORM_KEY = 'cookieConsent_details';
    public const LIST_KEY = 'cookieConsents';
    public const SECURITY_CONTEXT = 'sulu.cookieConsents.events';

    #[Groups(['cookie_consent:read'])]
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[Groups(['cookie_consent:read'])]
    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(type: 'string', length: 1024, nullable: true)]
    private ?string $userAgent = null;

    /**
     * @var string[]
     */
    #[Groups(['cookie_consent:read', 'cookie_consent:write'])]
    #[ORM\Column(type: 'json')]
    private array $preferences = [];

    public function __construct(?Uuid $id = null)
    {
        $this->id = $id ?? Uuid::v6();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getPreferences(): array
    {
        return $this->preferences;
    }

    public function setPreferences(array $preferences): self
    {
        $this->preferences = $preferences;

        return $this;
    }
}
