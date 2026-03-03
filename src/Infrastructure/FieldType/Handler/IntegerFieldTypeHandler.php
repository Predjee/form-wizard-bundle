<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType\Handler;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AbstractWizardFieldTypeHandler;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AsWizardFieldType;

#[AsWizardFieldType]
final class IntegerFieldTypeHandler extends AbstractWizardFieldTypeHandler
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    #[\Override]
    public function getKey(): string
    {
        return 'integer';
    }

    #[\Override]
    public function getSymfonyType(): string
    {
        return IntegerType::class;
    }

    #[\Override]
    public function getSuluProperties(): array
    {
        return [
            [
                'name' => 'min',
                'type' => 'number',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.integer.min',
                ],
                'colspan' => 6,
                'params' => [[
                    'name' => 'step',
                    'value' => '1',
                ]],
            ],
            [
                'name' => 'max',
                'type' => 'number',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.integer.max',
                ],
                'colspan' => 6,
                'params' => [[
                    'name' => 'step',
                    'value' => '1',
                ]],
            ],
        ];
    }

    #[\Override]
    public function getConstraints(array $config): array
    {
        $constraints = parent::getConstraints($config);

        $min = isset($config['min']) ? (int) $config['min'] : null;
        $max = isset($config['max']) ? (int) $config['max'] : null;

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
            'inputmode' => 'numeric',
        ];

        if (isset($config['min'])) {
            $attr['min'] = (int) $config['min'];
        }
        if (isset($config['max'])) {
            $attr['max'] = (int) $config['max'];
        }

        return [
            'required' => (bool) ($config['required'] ?? false),
            'attr' => $attr,
        ];
    }
}
