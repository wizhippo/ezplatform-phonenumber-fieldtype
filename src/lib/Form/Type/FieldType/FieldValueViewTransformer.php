<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoPhoneNumberFieldtype\Form\Type\FieldType;

use eZ\Publish\API\Repository\FieldType;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Wizhippo\WizhippoPhoneNumberFieldtype\FieldType\Value;

final class FieldValueViewTransformer implements DataTransformerInterface
{
    /**
     * @var \eZ\Publish\API\Repository\FieldType
     */
    private $fieldType;

    /**
     * @var string
     */
    private $defaultRegion;

    /**
     * @var int
     */
    private $format;

    /**
     * FieldValueViewTransformer constructor.
     *
     * @param \eZ\Publish\API\Repository\FieldType $fieldType
     * @param string                               $defaultRegion
     * @param int                                  $format
     */
    public function __construct(
        FieldType $fieldType,
        $defaultRegion = PhoneNumberUtil::UNKNOWN_REGION,
        $format = PhoneNumberFormat::INTERNATIONAL
    ) {
        $this->fieldType = $fieldType;
        $this->defaultRegion = $defaultRegion;
        $this->format = $format;
    }

    public function transform($value): string
    {
        if (null === $value) {
            return '';
        } elseif (!$value instanceof Value) {
            throw new TransformationFailedException(sprintf('Expected a %s.', Value::class));
        }

        if (null === $value->phoneNumber) {
            return '';
        }

        $util = PhoneNumberUtil::getInstance();
        if (PhoneNumberFormat::NATIONAL === $this->format) {
            return $util->formatOutOfCountryCallingNumber($value->phoneNumber, $this->defaultRegion);
        }

        return $util->format($value->phoneNumber, $this->format);
    }

    public function reverseTransform($string): Value
    {
        if (!$string && $string !== '0') {
            return $this->fieldType->getEmptyValue();
        }
        $util = PhoneNumberUtil::getInstance();
        try {
            return new Value($util->parse($string, $this->defaultRegion));
        } catch (NumberParseException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
