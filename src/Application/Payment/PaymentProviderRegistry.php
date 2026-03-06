<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Payment;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Yiggle\FormWizardBundle\Domain\Contract\Payment\PaymentProviderInterface;

/**
 * @internal Internal registry used to resolve configured payment providers.
 */
final class PaymentProviderRegistry implements PaymentProviderRegistryInterface
{
    /**
     * @var array<string, PaymentProviderInterface>
     */
    private array $providers = [];

    /**
     * @param iterable<PaymentProviderInterface> $providers
     */
    public function __construct(
        #[AutowireIterator(
            tag: 'yiggle_form_wizard.payment_provider',
            indexAttribute: 'alias'
        )]
        iterable $providers
    ) {
        foreach ($providers as $provider) {
            $this->providers[$provider->getAlias()] = $provider;
        }
    }

    public function enabled(): array
    {
        return array_filter(
            $this->providers,
            static fn (PaymentProviderInterface $p): bool => $p->isEnabled()
        );
    }

    public function hasEnabledProviders(): bool
    {
        return \count($this->enabled()) > 0;
    }

    public function get(string $alias): PaymentProviderInterface
    {
        if (! isset($this->providers[$alias])) {
            throw new \InvalidArgumentException(sprintf('Unknown payment provider "%s".', $alias));
        }

        return $this->providers[$alias];
    }

    public function all(): array
    {
        return $this->providers;
    }
}
