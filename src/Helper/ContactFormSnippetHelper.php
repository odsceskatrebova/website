<?php

declare(strict_types=1);

namespace App\Helper;

use App\Exception\InvalidSnippetDataException;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;

class ContactFormSnippetHelper
{
    /**
     * @param array<string, mixed> $content
     *
     * @throws InvalidSnippetDataException
     */
    public static function getStaffEmailFromSnippetContent(array $content): string
    {
        $staffEmail = $content['targetEmail'] ?? '';

        if (empty($staffEmail)) {
            throw new InvalidSnippetDataException('Staff email not found in snippet configuration. It must be set in the targetEmail field.');
        }

        $errors = Validation::createValidator()->validate($staffEmail, new Email());
        if (\count($errors) > 0) {
            throw new InvalidSnippetDataException('Invalid staff email in snippet configuration.');
        }

        return (string) $staffEmail;
    }

    /**
     * @param array<string, mixed> $content
     *
     * @throws InvalidSnippetDataException
     */
    public static function getSourceFromSnippetContent(array $content): string
    {
        $source = $content['source'] ?? '';

        if (empty($source)) {
            throw new InvalidSnippetDataException('Source not found in snippet configuration. It must be set in the source field.');
        }

        return (string) $source;
    }
}
