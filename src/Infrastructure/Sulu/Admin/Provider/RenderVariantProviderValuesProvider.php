<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Provider;

use Yiggle\FormWizardBundle\Application\View\WizardRenderVariant;

readonly class RenderVariantProviderValuesProvider
{
    /**
     * @return array<int<0, max>, array<string, string>>
     */
    public function getValues(): array
    {
        $renderVariants = WizardRenderVariant::cases();
        $values = [];
        foreach ($renderVariants as $renderVariant) {
            $values[] = [
                'name' => WizardRenderVariant::tryFrom($renderVariant->value)->value,
                'title' => ucfirst(WizardRenderVariant::tryFrom($renderVariant->value)->value),
            ];
        }
        return $values;
    }
}
