<?php

declare(strict_types=1);

namespace App\Validator;

use App\DTO\ContactForm;
use App\Service\SnippetService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactFormStructureValidator extends ConstraintValidator
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SnippetService $snippetService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ContactFormStructure) {
            throw new UnexpectedTypeException($constraint, ContactFormStructure::class);
        }

        if (!$value instanceof ContactForm) {
            throw new UnexpectedTypeException($value, ContactForm::class);
        }

        if (!\is_string($value->getFormUUID())) {
            throw new UnexpectedValueException($value->getFormUUID(), 'string');
        }

        if (!\is_string($value->getLocale())) {
            throw new UnexpectedValueException($value->getLocale(), 'string');
        }

        if (!\is_array($value->getFormData())) {
            throw new UnexpectedValueException($value->getFormData(), 'array');
        }

        $formData = $value->getFormData();
        $content = $this->snippetService->getSnippetContent($value->getFormUUID(), $value->getLocale());

        /** @var array<int, mixed> $formBlocks */
        $formBlocks = \is_array($content['formBlocks'] ?? null) ? $content['formBlocks'] : [];

        $this->logger->info('ContactFormStructureValidator - form data', $formData);

        foreach ($formBlocks as $block) {
            if (!\is_array($block)) {
                continue;
            }

            $type = \is_string($block['type'] ?? null) ? $block['type'] : '';

            if (str_contains($type, 'display')) {
                continue;
            }

            $key = \is_string($block['key'] ?? null) ? $block['key'] : '';

            if ('' === $key) {
                continue;
            }

            $this->logger->info('ContactFormStructureValidator - validating block', ['key' => $key, 'type' => $type]);

            if (!\array_key_exists($key, $formData)) {
                $this->context->buildViolation($constraint->missingParameterMessage)
                    ->setParameter('%s', $key)
                    ->setCode(ContactFormStructure::MISSING_PARAMETER_ERROR)
                    ->setTranslationDomain('validators')
                    ->addViolation();

                continue;
            }

            /** @var array<int, string> $validations */
            $validations = \is_array($block['validations'] ?? null) ? $block['validations'] : [];

            foreach ($validations as $rule) {
                if (!\is_string($rule)) {
                    continue;
                }

                if ('required' === $rule && empty($formData[$key])) {
                    $this->context->buildViolation($constraint->requiredValueMessage)
                        ->setParameter('%s', $key)
                        ->setCode(ContactFormStructure::REQUIRED_VALUE_ERROR)
                        ->setTranslationDomain('validators')
                        ->addViolation();
                }

                if ('email' === $rule && !empty($formData[$key])) {
                    $errors = $this->validator->validate((string) $formData[$key], new Email());
                    if (\count($errors) > 0) {
                        $this->context->buildViolation($constraint->invalidEmailMessage)
                            ->setParameter('%s', $key)
                            ->setCode(ContactFormStructure::INVALID_EMAIL_ERROR)
                            ->setTranslationDomain('validators')
                            ->addViolation();
                    }
                }

                if ('numeric' === $rule && !empty($formData[$key]) && !\is_numeric($formData[$key])) {
                    $this->context->buildViolation($constraint->numericValueMessage)
                        ->setParameter('%s', $key)
                        ->setCode(ContactFormStructure::NUMERIC_VALUE_ERROR)
                        ->setTranslationDomain('validators')
                        ->addViolation();
                }
            }
        }
    }
}
