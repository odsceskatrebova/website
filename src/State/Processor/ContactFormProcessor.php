<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\ContactForm;
use App\Helper\ContactFormHelper;
use App\Helper\ContactFormSnippetHelper;
use App\Service\FormMailerService;
use App\Service\SnippetService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactFormProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FormMailerService $formMailerService,
        private readonly EntityManagerInterface $entityManager,
        private readonly SnippetService $snippetService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ContactForm
    {
        \assert($data instanceof ContactForm);

        if (!empty($data->getHp())) {
            $this->logger->info('ContactFormProcessor - honeypot triggered, discarding submission');

            return $data;
        }

        $this->logger->debug('ContactFormProcessor - form data received', ['data' => $data]);

        try {
            $snippetContent = $this->snippetService->getSnippetContent($data->getFormUUID(), $data->getLocale());
        } catch (\Throwable $t) {
            $this->logger->error('ContactFormProcessor - unable to load snippet', [
                'exception' => $t::class,
                'message' => $t->getMessage(),
            ]);

            throw new HttpException(404, $this->translator->trans('error.formConfigurationNotFound', [], null, $data->getLocale()));
        }

        $source = (string) ($snippetContent['source'] ?? '');

        try {
            $this->formMailerService->sendFormEmailToStaff($data, $snippetContent, $source);
        } catch (\Throwable $t) {
            $this->logger->error('ContactFormProcessor - unable to send staff email', [
                'exception' => $t::class,
                'message' => $t->getMessage(),
            ]);
        }

        if ('contactForm' === $source) {
            try {
                if (null !== ContactFormHelper::findAndValidateEmail($data)) {
                    $this->formMailerService->sendFormConfirmationEmail($data, $snippetContent);
                }
            } catch (\Throwable $t) {
                $this->logger->error('ContactFormProcessor - unable to send confirmation email', [
                    'exception' => $t::class,
                    'message' => $t->getMessage(),
                ]);
            }
        }

        if ('newsletterSubscription' === $source) {
            try {
                if (null !== ContactFormHelper::findAndValidateEmail($data)) {
                    $this->formMailerService->sendSubscribeConfirmationEmail($data, $snippetContent);
                }
            } catch (\Throwable $t) {
                $this->logger->error('ContactFormProcessor - unable to send subscribe confirmation email', [
                    'exception' => $t::class,
                    'message' => $t->getMessage(),
                ]);
            }
        }

        $lead = $data->toLead(ContactFormSnippetHelper::getSourceFromSnippetContent($snippetContent));
        $this->entityManager->persist($lead);
        $this->entityManager->flush();

        return $data;
    }
}
