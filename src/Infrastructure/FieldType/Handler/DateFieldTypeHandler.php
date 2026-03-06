<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType\Handler;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AbstractWizardFieldTypeHandler;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AsWizardFieldType;

/**
 * @internal Built-in field type handler.
 */
#[AsWizardFieldType]
final class DateFieldTypeHandler extends AbstractWizardFieldTypeHandler
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[\Override]
    public function getKey(): string
    {
        return 'date';
    }

    #[\Override]
    public function getSymfonyType(): string
    {
        return DateType::class;
    }

    #[\Override]
    public function getConstraints(array $config): array
    {
        $constraints = parent::getConstraints($config);

        $constraints[] = new Assert\Type(\DateTimeInterface::class);

        if (! empty($config['min']) && is_string($config['min'])) {
            $min = \DateTimeImmutable::createFromFormat('Y-m-d', $config['min']) ?: null;
            if ($min) {
                $constraints[] = new Assert\GreaterThanOrEqual(value: $min, message: $this->translator->trans('yiggle_form_wizard.admin.field.date.min', [], 'yiggle_form_wizard'));
            }
        }

        if (! empty($config['max']) && is_string($config['max'])) {
            $max = \DateTimeImmutable::createFromFormat('Y-m-d', $config['max']) ?: null;
            if ($max) {
                $constraints[] = new Assert\LessThanOrEqual(value: $max, message: $this->translator->trans('yiggle_form_wizard.admin.field.date.max', [], 'yiggle_form_wizard'));
            }
        }

        return $constraints;
    }

    #[\Override]
    public function getSuluProperties(): array
    {
        return [
            [
                'name' => 'min',
                'type' => 'text_line',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.date.min',
                ],
                'colspan' => 6,
            ],
            [
                'name' => 'max',
                'type' => 'text_line',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.date.max',
                ],
                'colspan' => 6,
            ],
        ];
    }

    #[\Override]
    public function buildSymfonyOptions(array $config): array
    {
        $attr = [];

        if (! empty($config['min'])) {
            $attr['min'] = (string) $config['min'];
        }
        if (! empty($config['max'])) {
            $attr['max'] = (string) $config['max'];
        }

        return [
            'required' => (bool) ($config['required'] ?? false),
            'widget' => 'single_text',
            'html5' => true,
            'attr' => $attr,
        ];
    }
}
