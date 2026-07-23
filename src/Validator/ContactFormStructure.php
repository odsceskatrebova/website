<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ContactFormStructure extends Constraint
{
    public string $missingParameterMessage = 'Contact form missing parameter: %s';
    public const MISSING_PARAMETER_ERROR = '154e4276-d5d0-49f0-b1cb-62d03c6a0a4a';

    public string $requiredValueMessage = '%s is required';
    public const REQUIRED_VALUE_ERROR = '59a0f14a-0edb-461d-80c5-8f672eac8be5';

    public string $invalidEmailMessage = '%s must be a valid email address';
    public const INVALID_EMAIL_ERROR = '926aee0e-9d73-453d-99ab-ada7308d896f';

    public string $numericValueMessage = '%s must be a number';
    public const NUMERIC_VALUE_ERROR = 'ae983653-073e-46aa-b203-5dc6bc1eea18';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
