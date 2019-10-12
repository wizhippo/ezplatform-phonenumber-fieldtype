<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoPhoneNumberFieldtype\FieldType;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;

class Value extends BaseValue
{
    /** @var \libphonenumber\PhoneNumber */
    public $phoneNumber;

    /**
     * Construct a new Value object and initialize with $phoneNumber.
     *
     * @param \libphonenumber\PhoneNumber|null $phoneNumber Date as a PhoneNumber object
     */
    public function __construct(?PhoneNumber $phoneNumber = null)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Creates a Value from the given $phoneNumberString.
     *
     * @param string $phoneNumberString
     *
     * @return \Wizhippo\WizhippoPhoneNumberFieldtype\FieldType\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     */
    public static function fromString(string $phoneNumberString): ?self
    {
        $util = PhoneNumberUtil::getInstance();
        try {
            return new static($util->parse($phoneNumberString));
        } catch (NumberParseException $e) {
            throw new InvalidArgumentValue('$phoneNumberString', $phoneNumberString, __CLASS__, $e);
        }
    }

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString(): string
    {
        if (!$this->phoneNumber instanceof PhoneNumber) {
            return '';
        }

        return (string)$this->phoneNumber;
    }
}
