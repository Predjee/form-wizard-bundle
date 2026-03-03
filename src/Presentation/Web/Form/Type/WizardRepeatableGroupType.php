<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yiggle\FormWizardBundle\Presentation\Web\Form\EventListener\RepeatableRowListener;

/**
 * @extends AbstractType<mixed>
 */
final class WizardRepeatableGroupType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new RepeatableRowListener($options['fields_config']));

        $builder->add('add_row', SubmitType::class, [
            'label' => $options['fields_config']['addLabel'] ?? 'yiggle_form_wizard.wizard.add_row',
            'validation_groups' => false,
            'attr' => [
                'formnovalidate' => 'formnovalidate',
            ],
        ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('fields_config');
        $resolver->setDefaults([
            'data_class' => null,
            'compound' => true,
        ]);
    }
}
