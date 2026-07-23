<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ContactForm;
use App\Exception\InvalidSnippetDataException;
use App\Helper\ContactFormHelper;
use App\Helper\ContactFormSnippetHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as Twig;

class FormMailerService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly Twig $twig,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendEmail(string $to, string $subject, string $text, string $html, ?string $replyTo = null): void
    {
        $email = (new Email())
            ->to($to)
            ->subject($subject)
            ->text($text)
            ->html($html);

        if ($replyTo) {
            $email->replyTo($replyTo);
        }

        $this->mailer->send($email);

        $this->logger->info('Email sent', ['to' => $to, 'subject' => $subject]);
    }

    /**
     * @param array<string, mixed> $snippetContent
     *
     * @throws TransportExceptionInterface
     * @throws InvalidSnippetDataException
     * @throws \Throwable
     */
    public function sendFormEmailToStaff(ContactForm $contactForm, array $snippetContent, string $source): void
    {
        $staffEmail = ContactFormSnippetHelper::getStaffEmailFromSnippetContent($snippetContent);
        $customerEmail = ContactFormHelper::findAndValidateEmail($contactForm);

        $options = [
            'data' => $contactForm,
            'source' => $source,
            'formTemplate' => $snippetContent['formBlocks'] ?? [],
            'lang' => 'en',
        ];

        $this->sendEmail(
            to: $staffEmail,
            subject: $this->translator->trans('form.formEmailToStaff.subject', [], 'email'),
            text: $this->twig->render('email/formEmailToStaff.txt.twig', $options),
            html: $this->twig->render('email/formEmailToStaff.html.twig', $options),
            replyTo: $customerEmail,
        );
    }

    /**
     * @param array<string, mixed> $snippetContent
     *
     * @throws TransportExceptionInterface
     * @throws \Throwable
     */
    public function sendFormConfirmationEmail(ContactForm $contactForm, array $snippetContent): void
    {
        $customerEmail = ContactFormHelper::findAndValidateEmail($contactForm);

        $options = [
            'data' => $contactForm,
            'lang' => $contactForm->getLocale(),
        ];

        $this->sendEmail(
            to: $customerEmail,
            subject: $this->translator->trans('form.formConfirmationEmail.subject', [], 'email'),
            text: $this->twig->render('email/formConfirmationEmail.txt.twig', $options),
            html: $this->twig->render('email/formConfirmationEmail.html.twig', $options),
        );
    }

    /**
     * @param array<string, mixed> $snippetContent
     *
     * @throws TransportExceptionInterface
     * @throws \Throwable
     */
    public function sendSubscribeConfirmationEmail(ContactForm $contactForm, array $snippetContent): void
    {
        $customerEmail = ContactFormHelper::findAndValidateEmail($contactForm);

        $options = [
            'data' => $contactForm,
            'lang' => $contactForm->getLocale(),
        ];

        $this->sendEmail(
            to: $customerEmail,
            subject: $this->translator->trans('form.subscribeConfirmationEmail.subject', [], 'email'),
            text: $this->twig->render('email/subscribeConfirmationEmail.txt.twig', $options),
            html: $this->twig->render('email/subscribeConfirmationEmail.html.twig', $options),
        );
    }
}
