<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Payment;

use Yiggle\FormWizardBundle\Domain\Contract\Payment\PaymentProviderInterface;

interface PaymentProviderRegistryInterface
{
    /**
     * @return array<string, PaymentProviderInterface>
     */
    public function enabled(): array;

    public function hasEnabledProviders(): bool;

    public function get(string $alias): PaymentProviderInterface;

    /**
     * @return array<string, PaymentProviderInterface>
     */
    public function all(): array;
}
