<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\WizardFieldTypeRegistry;
use Yiggle\FormWizardBundle\Presentation\Web\Form\Receipt\ReceiptTriggerDecider;

/**
 * @extends AbstractType<mixed>
 */
final class WizardRepeatableRowType extends AbstractType
{
    public function __construct(
        private readonly WizardFieldTypeRegistry $registry,
        private readonly ReceiptTriggerDecider $receiptTriggerDecider,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $rowFieldConfigs = $options['row_fields_config'];

        foreach ($rowFieldConfigs as $fieldConfig) {
            $type = (string) ($fieldConfig['type'] ?? '');
            $name = (string) ($fieldConfig['name'] ?? '');

            if ($type === '' || $name === '' || ! $this->registry->has($type)) {
                continue;
            }

            $handler = $this->registry->get($type);

            $opts = $handler->buildSymfonyOptions($fieldConfig);
            $opts['label'] = $fieldConfig['label'] ?? $opts['label'] ?? $name;
            $opts['required'] = (bool) ($fieldConfig['required'] ?? $opts['required'] ?? false);
            $opts['constraints'] = $handler->getConstraints($fieldConfig);

            $width = (int) ($fieldConfig['width'] ?? 12);
            $opts['row_attr'] = array_merge($opts['row_attr'] ?? [], [
                'data-yw-width' => $width,
                'class' => trim(($opts['row_attr']['class'] ?? '') . ' yw-field-wrapper'),
            ]);

            $opts['attr'] ??= [];
            $opts['attr'] = array_merge($opts['attr'], [
                'data-yw-width' => $width,
            ]);

            if ($this->receiptTriggerDecider->shouldAttach($handler, $fieldConfig)) {
                $opts['attr'] = $this->receiptTriggerDecider->withReceiptTriggerAttr($opts['attr']);
            }

            /** @var class-string<FormTypeInterface<mixed>> $symfonyType */
            $symfonyType = $handler->getSymfonyType();
            $builder->add($name, $symfonyType, $opts);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('row_fields_config');

        $resolver->setDefaults([
            'translation_domain' => 'yiggle_form_wizard',
            'data_class' => null,
            'required' => false,
            'compound' => true,
        ]);

        $resolver->setAllowedTypes('row_fields_config', 'array');
    }
}
