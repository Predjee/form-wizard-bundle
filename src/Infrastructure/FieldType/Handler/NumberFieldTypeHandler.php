<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType\Handler;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AbstractWizardFieldTypeHandler;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AsWizardFieldType;

#[AsWizardFieldType]
final class NumberFieldTypeHandler extends AbstractWizardFieldTypeHandler
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    #[\Override]
    public function getKey(): string
    {
        return 'number';
    }

    #[\Override]
    public function getSymfonyType(): string
    {
        return NumberType::class;
    }

    #[\Override]
    public function getSuluProperties(): array
    {
        return [
            [
                'name' => 'min',
                'type' => 'number',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.number.min',
                ],
                'colspan' => 4,
            ],
            [
                'name' => 'max',
                'type' => 'number',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.number.max',
                ],
                'colspan' => 4,
            ],
            [
                'name' => 'step',
                'type' => 'number',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.number.step',
                ],
                'colspan' => 4,
                'params' => [[
                    'name' => 'step',
                    'value' => '0.01',
                ]],
            ],
        ];
    }

    #[\Override]
    public function getConstraints(array $config): array
    {
        $constraints = parent::getConstraints($config);

        $min = isset($config['min']) ? (float) $config['min'] : null;
        $max = isset($config['max']) ? (float) $config['max'] : null;

        if ($min !== null || $max !== null) {
            $constraints[] = new Assert\Range(
                minMessage: $this->translator->trans('yiggle_form_wizard.admin.field.range.min', [], 'yiggle_form_wizard'),
                maxMessage: $this->translator->trans('yiggle_form_wizard.admin.field.range.max', [], 'yiggle_form_wizard'),
                min: $min,
                max: $max,
            );
        }

        return $constraints;
    }

    #[\Override]
    public function buildSymfonyOptions(array $config): array
    {
        $attr = [
            'inputmode' => 'decimal',
        ];
        if (isset($config['min'])) {
            $attr['min'] = (float) $config['min'];
        }
        if (isset($config['max'])) {
            $attr['max'] = (float) $config['max'];
        }
        if (isset($config['step'])) {
            $attr['step'] = (float) $config['step'];
        }

        return [
            'required' => (bool) ($config['required'] ?? false),
            'attr' => $attr,
        ];
    }
}
