<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Payment\Attribute;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @internal
 */
#[AutoconfigureTag('yiggle_form_wizard.payment_provider')]
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsPaymentProvider
{
    public function __construct(
        public ?string $alias = null
    ) {
    }
}
