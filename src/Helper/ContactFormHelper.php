<?php

declare(strict_types=1);

namespace App\Helper;

use App\DTO\ContactForm;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;

class ContactFormHelper
{
    public static function findAndValidateEmail(ContactForm $contactForm): ?string
    {
        $value = $contactForm->getFormData()['email'] ?? null;

        if (null === $value) {
            return null;
        }

        $errors = Validation::createValidator()->validate($value, new Email());
        if (\count($errors) > 0) {
            return null;
        }

        return (string) $value;
    }

    public static function findPhoneNumber(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['phone']) ? (string) $contactForm->getFormData()['phone'] : null;
    }

    public static function findName(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['name']) ? (string) $contactForm->getFormData()['name'] : null;
    }

    public static function findMessage(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['message']) ? (string) $contactForm->getFormData()['message'] : null;
    }

    public static function findOrgName(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['org_name']) ? (string) $contactForm->getFormData()['org_name'] : null;
    }

    public static function findOrgNumber(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['org_number']) ? (string) $contactForm->getFormData()['org_number'] : null;
    }

    public static function findVatNumber(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['vat_number']) ? (string) $contactForm->getFormData()['vat_number'] : null;
    }

    public static function findNote(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['note']) ? (string) $contactForm->getFormData()['note'] : null;
    }

    public static function findStreet(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['street']) ? (string) $contactForm->getFormData()['street'] : null;
    }

    public static function findCity(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['city']) ? (string) $contactForm->getFormData()['city'] : null;
    }

    public static function findProvince(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['province']) ? (string) $contactForm->getFormData()['province'] : null;
    }

    public static function findDistrict(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['district']) ? (string) $contactForm->getFormData()['district'] : null;
    }

    public static function findZipCode(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['zip_code']) ? (string) $contactForm->getFormData()['zip_code'] : null;
    }

    public static function findCountry(ContactForm $contactForm): ?string
    {
        return isset($contactForm->getFormData()['country']) ? (string) $contactForm->getFormData()['country'] : null;
    }
}
