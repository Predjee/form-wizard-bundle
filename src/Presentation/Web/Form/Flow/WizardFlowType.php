<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Form\Flow;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Flow\AbstractFlowType;
use Symfony\Component\Form\Flow\FormFlowBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;
use Yiggle\FormWizardBundle\Infrastructure\DataStorage\MergingSessionDataStorage;
use Yiggle\FormWizardBundle\Presentation\Web\Form\Type\DynamicWizardStepType;
use Yiggle\FormWizardBundle\Presentation\Web\Form\Type\WizardNavigatorType;

final class WizardFlowType extends AbstractFlowType
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['wizard']);
        $resolver->setAllowedTypes('wizard', WizardFormInterface::class);

        $resolver->setDefaults([
            'data_class' => null,
            'step_property_path' => '[currentStep]',
            'data_storage' => fn (Options $options): MergingSessionDataStorage => new MergingSessionDataStorage(
                'wizard_' . $options['wizard']->getUuid(),
                $this->requestStack
            ),
        ]);
    }

    #[\Override]
    public function buildFormFlow(FormFlowBuilderInterface $builder, array $options): void
    {
        /** @var WizardFormInterface $wizard */
        $wizard = $options['wizard'];

        $steps = \iterator_to_array($wizard->getSteps(), false);
        usort(
            $steps,
            /** @param WizardStepInterface $a @param WizardStepInterface $b */
            static fn ($a, $b): int => $a->getPosition() <=> $b->getPosition()
        );

        foreach ($steps as $stepEntity) {
            $uuid = $stepEntity->getUuid();

            $builder->addStep($uuid, DynamicWizardStepType::class, [
                'label' => $stepEntity->getTitle(),
                'help' => $stepEntity->getStepInstruction(),
                'translation_domain' => 'yiggle_form_wizard',
                'wizard_step' => $stepEntity,
                'property_path' => '[' . $uuid . ']',
            ]);
        }

        $builder->add('_wizard_id', HiddenType::class, [
            'data' => $wizard->getUuid(),
            'mapped' => false,
        ]);

        $builder->add('navigator', WizardNavigatorType::class, [
            'label_finish' => $wizard->getSubmitLabel() ?: 'yiggle_form_wizard.wizard.finish',
        ]);
    }
}
