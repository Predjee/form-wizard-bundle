<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Flow\FormFlowCursor;
use Symfony\Component\Form\Flow\Type\FinishFlowType;
use Symfony\Component\Form\Flow\Type\NextFlowType;
use Symfony\Component\Form\Flow\Type\PreviousFlowType;
use Symfony\Component\Form\Flow\Type\ResetFlowType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class WizardNavigatorType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('reset', ResetFlowType::class, [
            'label' => $options['label_reset'],
            'include_if' => static fn (FormFlowCursor $cursor): bool => ! $cursor->isFirstStep(),
        ]);

        $builder->add('back', PreviousFlowType::class, [
            'validate' => false,
            'label' => $options['label_back'],
            'include_if' => static fn (FormFlowCursor $cursor): bool => ! $cursor->isFirstStep(),
        ]);

        $builder->add('next', NextFlowType::class, [
            'label' => $options['label_next'],
            'include_if' => static fn (FormFlowCursor $cursor): bool => ! $cursor->isLastStep(),
        ]);

        $builder->add('finish', FinishFlowType::class, [
            'label' => $options['label_finish'],
            'include_if' => static fn (FormFlowCursor $cursor): bool => $cursor->isLastStep(),
            'attr' => [
                'data-turbo' => 'false',
            ],
        ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'priority' => -100,
            'translation_domain' => 'yiggle_form_wizard',

            'label_reset' => 'yiggle_form_wizard.wizard.reset',
            'label_back' => 'yiggle_form_wizard.wizard.prev',
            'label_next' => 'yiggle_form_wizard.wizard.next',
            'label_finish' => 'yiggle_form_wizard.wizard.finish',
        ]);

        $resolver->setAllowedTypes('label_reset', 'string');
        $resolver->setAllowedTypes('label_back', 'string');
        $resolver->setAllowedTypes('label_next', 'string');
        $resolver->setAllowedTypes('label_finish', 'string');
    }
}
