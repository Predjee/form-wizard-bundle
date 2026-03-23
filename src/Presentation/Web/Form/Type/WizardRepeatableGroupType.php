<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class WizardRepeatableGroupType extends AbstractType
{
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['fields_config'] = $options['fields_config'];
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('fields_config');

        $resolver->setDefaults([
            'entry_type' => WizardRepeatableRowType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'prototype_name' => '__name__',
            'by_reference' => false,
            'data_class' => null,
            'compound' => true,
            'empty_data' => static fn () => [[]],
            'entry_options' => static fn (Options $options): array => [
                'row_fields_config' => $options['fields_config']['rowFields'] ?? [],
            ],
        ]);
    }

    #[\Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
