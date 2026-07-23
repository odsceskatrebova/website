<?php

declare(strict_types=1);

namespace App\DTO;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Entity\Lead;
use App\Helper\ContactFormHelper;
use App\State\Processor\ContactFormProcessor;
use App\Validator\ContactFormStructure;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ContactFormStructure]
#[ApiResource(operations: [
    new Post(
        uriTemplate: '/contact_form',
        normalizationContext: ['groups' => ['form:read']],
        denormalizationContext: ['groups' => ['form:write']],
        processor: ContactFormProcessor::class,
    ),
])]
class ContactForm
{
    private readonly \DateTime $createdAt;

    public function __construct(
        #[Groups(['form:write'])]
        #[Assert\NotBlank]
        private readonly string $formUUID,
        #[Groups(['form:write'])]
        #[Assert\NotBlank]
        private readonly string $locale,
        /**
         * @var array<string, string|bool|float|int>
         */
        #[Groups(['form:write'])]
        #[Assert\NotBlank]
        private readonly array $formData,
        #[Groups(['form:write'])]
        private readonly ?string $hp = null,
    ) {
        $this->createdAt = new \DateTime();
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getFormUUID(): string
    {
        return $this->formUUID;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return array<string, string|bool|float|int>
     */
    public function getFormData(): array
    {
        return $this->formData;
    }

    public function getHp(): ?string
    {
        return $this->hp;
    }

    public function toLead(string $source): Lead
    {
        $lead = new Lead();
        $lead->setSource($source)
            ->setName(ContactFormHelper::findName($this))
            ->setMessage(ContactFormHelper::findMessage($this))
            ->setEmail(ContactFormHelper::findAndValidateEmail($this))
            ->setPhone(ContactFormHelper::findPhoneNumber($this))
            ->setPayload($this->formData);

        return $lead;
    }
}
