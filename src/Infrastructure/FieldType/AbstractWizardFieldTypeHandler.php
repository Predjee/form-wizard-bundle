<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType;

use Symfony\Component\Validator\Constraints as Assert;
use Yiggle\FormWizardBundle\Domain\Contract\FieldType\WizardFieldTypeHandlerInterface;

abstract class AbstractWizardFieldTypeHandler implements WizardFieldTypeHandlerInterface
{
    #[\Override]
    public function getConstraints(array $config): array
    {
        $constraints = [];

        if (($config['required'] ?? false) === true && $this->shouldUseNotBlankForRequired()) {
            $constraints[] = new Assert\NotBlank();
        }

        return $constraints;
    }

    #[\Override]
    public function isAllowedInsideRepeatableGroup(): bool
    {
        return true;
    }

    protected function shouldUseNotBlankForRequired(): bool
    {
        return true;
    }

    /**
     * @return array<array<string, mixed>>
     */
    protected function commonSuluTextProps(string $prefixKey): array
    {
        return [
            [
                'name' => 'placeholder',
                'type' => 'text_line',
                'meta' => [
                    'title' => $prefixKey . '.placeholder',
                ],
                'colspan' => 6,
            ],
            [
                'name' => 'help',
                'type' => 'text_line',
                'meta' => [
                    'title' => $prefixKey . '.help',
                ],
                'colspan' => 6,
            ],
        ];
    }
}
