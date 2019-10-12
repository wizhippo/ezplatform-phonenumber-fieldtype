<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoPhoneNumberFieldtype\FieldType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class Type extends FieldType
{
    /**
     * {@inheritdoc}
     */
    protected $settingsSchema = [
        'defaultRegion' => [
            'type' => 'string',
            'default' => PhoneNumberUtil::UNKNOWN_REGION,
        ],
        'format' => [
            'type' => 'integer',
            'default' => PhoneNumberFormat::INTERNATIONAL,
        ],
    ];

    public function setDefaultRegion($defaultRegion = PhoneNumberUtil::UNKNOWN_REGION)
    {
        $this->settingsSchema['defaultRegion']['default'] = $defaultRegion;
    }

    public function setFormat($format = PhoneNumberFormat::INTERNATIONAL)
    {
        $this->settingsSchema['format']['default'] = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezphonenumber';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(SPIValue $value, FieldDefinition $fieldDefinition, string $languageCode): string
    {
        if ($this->isEmptyValue($value)) {
            return '';
        }

        return (string)$value->phoneNumber;
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \Wizhippo\WizhippoPhoneNumberFieldtype\FieldType\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param string|\Wizhippo\WizhippoPhoneNumberFieldtype\FieldType\Value $inputValue
     *
     * @return \Wizhippo\WizhippoPhoneNumberFieldtype\FieldType\Value potentially converted and structurally plausible
     *     value.
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_string($inputValue)) {
            $inputValue = Value::fromString($inputValue);
        }

        if ($inputValue instanceof PhoneNumber) {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @param \Wizhippo\WizhippoPhoneNumberFieldtype\FieldType\Value $value
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected
     *     structure.
     *
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (!$value->phoneNumber instanceof PhoneNumber) {
            throw new InvalidArgumentType(
                '$value->phoneNumber',
                PhoneNumber::class,
                $value->phoneNumber
            );
        }
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \Wizhippo\WizhippoPhoneNumberFieldtype\FieldType\Value $value
     *
     * @return null|string
     */
    protected function getSortInfo(BaseValue $value): ?string
    {
        if ($value->phoneNumber === null) {
            return null;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        return $phoneUtil->format($value->phoneNumber, PhoneNumberFormat::INTERNATIONAL);
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \Wizhippo\WizhippoPhoneNumberFieldtype\FieldType\Value $value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function fromHash($hash)
    {
        if ($hash === null || empty($hash['rfc3966'])) {
            return $this->getEmptyValue();
        }

        return Value::fromString($hash['rfc3966']);
    }

    /**
     * Converts a $value to a hash.
     *
     * @param \Wizhippo\WizhippoPhoneNumberFieldtype\FieldType\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value): ?array
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        if ($value->phoneNumber instanceof PhoneNumber) {
            $util = PhoneNumberUtil::getInstance();

            return [
                'rfc3966' => $util->format($value->phoneNumber, PhoneNumberFormat::RFC3966),
            ];
        }

        return [
            'rfc3966' => null,
        ];
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateFieldSettings($fieldSettings): array
    {
        $defaultRegion = $fieldSettings['defaultRegion'] ?? PhoneNumberUtil::UNKNOWN_REGION;
        $format = $fieldSettings['format'] ?? PhoneNumberFormat::NATIONAL;
        $errors = [];

        if (!is_string($defaultRegion) || $defaultRegion === '') {
            $errors[] = new ValidationError(
                'Value must be a string.',
                null,
                [],
                'defaultRegion'
            );
        }

        if (!is_numeric($format) || $format > PhoneNumberFormat::RFC3966) {
            $errors[] = new ValidationError(
                'Value must be numeric PhoneNumberFormat value.',
                null,
                [],
                'format'
            );
        }

        return $errors;
    }
}
