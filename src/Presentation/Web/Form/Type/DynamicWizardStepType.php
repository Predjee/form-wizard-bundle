<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\WizardFieldTypeRegistry;
use Yiggle\FormWizardBundle\Presentation\Web\Form\Receipt\ReceiptTriggerDecider;

/**
 * @extends AbstractType<mixed>
 */
final class DynamicWizardStepType extends AbstractType
{
    public function __construct(
        private readonly WizardFieldTypeRegistry $registry,
        private readonly ReceiptTriggerDecider $receiptTriggerDecider,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var WizardStepInterface $step */
        $step = $options['wizard_step'];

        foreach ($step->getStepFields() as $stepField) {
            $wizardField = $stepField->getField();
            $name = $wizardField->getName();
            $config = $wizardField->getConfig();
            $handler = $this->registry->get($wizardField->getType());

            $fieldOptions = $handler->buildSymfonyOptions($config);
            $fieldOptions['label'] = $config['label'] ?? $wizardField->getLabel();
            $fieldOptions['required'] = $stepField->isRequired();
            $fieldOptions['constraints'] = $handler->getConstraints([
                ...$config,
                'required' => $stepField->isRequired(),
            ]);

            $col = $stepField->getWidth() ?: 12;
            $fieldOptions['row_attr'] = array_replace($fieldOptions['row_attr'] ?? [], [
                'class' => trim(($fieldOptions['row_attr']['class'] ?? '') . ' yw-field'),
                'data-yw-width' => $col,
            ]);

            if ($this->receiptTriggerDecider->shouldAttach($handler, $config)) {
                $fieldOptions['attr'] = $this->receiptTriggerDecider->withReceiptTriggerAttr($fieldOptions['attr'] ?? []);
            }

            /** @var class-string<FormTypeInterface<mixed>> $type */
            $type = $handler->getSymfonyType();
            $builder->add($name, $type, $fieldOptions);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('wizard_step');
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
