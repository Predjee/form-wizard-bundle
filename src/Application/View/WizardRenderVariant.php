<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\View;

enum WizardRenderVariant: string
{
    case Card = 'card';
    case Inline = 'inline';

    public function template(): string
    {
        return sprintf('@YiggleFormWizard/components/wizard/variants/%s.html.twig', $this->value);
    }

    public function label(): string
    {
        return match ($this) {
            self::Card => 'Card',
            self::Inline => 'Inline',
        };
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return [self::Card, self::Inline];
    }
}
