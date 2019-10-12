<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoPhoneNumberFieldtypeBundle\Twig\Extension;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PhoneNumberHelperExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('phone_number_format', [$this, 'formatPhoneNumber']),
        ];
    }

    public function formatPhoneNumber(PhoneNumber $phoneNumber, $format = PhoneNumberFormat::INTERNATIONAL)
    {
        if (is_string($format)) {
            $constant = '\libphonenumber\PhoneNumberFormat::' . $format;
            if (!defined($constant)) {
                throw new InvalidArgumentException('$format',
                    'The format must be either a constant value or name in libphonenumber\PhoneNumberFormat');
            }
            $format = constant('\libphonenumber\PhoneNumberFormat::' . $format);
        }

        $util = PhoneNumberUtil::getInstance();

        return $util->format($phoneNumber, $format);
    }
}
