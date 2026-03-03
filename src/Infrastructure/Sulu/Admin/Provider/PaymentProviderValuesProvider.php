<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Provider;

use Yiggle\FormWizardBundle\Application\Payment\PaymentProviderRegistry;

readonly class PaymentProviderValuesProvider
{
    public function __construct(
        private PaymentProviderRegistry $registry
    ) {
    }

    /**
     * @return array<int<0, max>, array<string, string>>
     */
    public function getValues(): array
    {
        $providers = $this->registry->all();
        $values = [];
        foreach ($providers as $provider) {
            $values[] = [
                'name' => $provider->getAlias(),
                'title' => ucfirst($provider->getAlias()),
            ];
        }
        return $values;
    }
}
