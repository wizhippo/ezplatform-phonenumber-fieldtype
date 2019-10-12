<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoPhoneNumberFieldtype\FieldType\Mapper;

use EzSystems\RepositoryForms\Data\Content\FieldData;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\FieldDefinitionFormMapperInterface;
use EzSystems\RepositoryForms\FieldType\FieldValueFormMapperInterface;
use libphonenumber\PhoneNumberFormat;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Wizhippo\WizhippoPhoneNumberFieldtype\Form\Type\FieldType\PhoneNumberFieldType;

class PhoneNumberFormMapper implements FieldDefinitionFormMapperInterface, FieldValueFormMapperInterface
{
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        $isTranslation = $data->contentTypeData->languageCode !== $data->contentTypeData->mainLanguageCode;
        $fieldDefinitionForm
            ->add('defaultRegion', TextType::class, [
                'required' => false,
                'property_path' => 'fieldSettings[defaultRegion]',
                'label' => /** @Desc("Default region") */ 'field_definition.ezphonenumber.defaultregion',
                'translation_domain' => 'phonenumber_fieldtype',
                'disabled' => $isTranslation,
            ])
            ->add('format', ChoiceType::class, [
                'required' => false,
                'property_path' => 'fieldSettings[format]',
                'label' => /** @Desc("Format") */ 'field_definition.ezphonenumber.format',
                'translation_domain' => 'phonenumber_fieldtype',
                'disabled' => $isTranslation,
                'choices' => [
                    'E164' => PhoneNumberFormat::E164,
                    'INTERNATIONAL' => PhoneNumberFormat::INTERNATIONAL,
                    'NATIONAL' => PhoneNumberFormat::NATIONAL,
                    'RFC3966' => PhoneNumberFormat::RFC3966,
                ]
            ]);
    }

    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        PhoneNumberFieldType::class, [
                            'label' => $fieldDefinition->getName(),
                            'required' => $fieldDefinition->isRequired,
                            'defaultRegion' => $fieldDefinition->fieldSettings['defaultRegion'],
                            'format' => $fieldDefinition->fieldSettings['format'],
                        ]
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
