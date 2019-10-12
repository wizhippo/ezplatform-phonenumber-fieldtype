<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoPhoneNumberFieldtype\Form\Type\FieldType;

use eZ\Publish\API\Repository\FieldTypeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PhoneNumberFieldType extends AbstractType
{
    /** @var FieldTypeService */
    private $fieldTypeService;

    public function __construct(FieldTypeService $fieldTypeService)
    {
        $this->fieldTypeService = $fieldTypeService;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'compound' => false,
                'invalid_message' => 'This value is not a valid phone number.',
            ]
        );

        $resolver->setRequired(['defaultRegion']);
        $resolver->setAllowedTypes('defaultRegion', 'string');
        $resolver->setRequired(['format']);
        $resolver->setAllowedTypes('format', 'integer');

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addViewTransformer(
                new FieldValueViewTransformer(
                    $this->fieldTypeService->getFieldType('ezphonenumber'),
                    $options['defaultRegion'],
                    $options['format']
                )
            );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['type'] = 'tel';
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezphonenumber';
    }
}
