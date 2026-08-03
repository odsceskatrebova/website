<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LeadRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: LeadRepository::class)]
#[ORM\Table(name: '`lead`')]
#[ORM\HasLifecycleCallbacks]
class Lead
{
    public const RESOURCE_KEY = 'leads';
    public const FORM_KEY = 'lead_details';
    public const LIST_KEY = 'leads';
    public const SECURITY_CONTEXT = 'sulu.leads.events';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 512, nullable: true)]
    private ?string $source = null;

    #[ORM\Column(type: 'string', length: 512, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 512, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 512, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 512, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $createdAt;

    /**
     * @var mixed[]
     */
    #[ORM\Column(type: 'json')]
    private array $payload = [];

    public function __construct(?Uuid $id = null)
    {
        $this->id = $id ?? Uuid::v6();
    }

    #[ORM\PrePersist]
    public function autoAddCreatedAt(): void
    {
        if (isset($this->createdAt)) {
            return;
        }

        $this->setCreatedAt(new DateTime());
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
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

    /**
     * @return mixed[]
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param mixed[] $payload
     */
    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }
}
