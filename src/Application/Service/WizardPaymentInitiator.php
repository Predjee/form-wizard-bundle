<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Yiggle\FormWizardBundle\Application\Contract\EventBusInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardSubmissionRepositoryInterface;
use Yiggle\FormWizardBundle\Application\Event\WizardPaymentInitiatedEvent;
use Yiggle\FormWizardBundle\Application\Payment\PaymentProviderRegistryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;

/**
 * @internal Responsible for initiating payment flows. This class is an internal
 *           orchestration service and not part of the public API.
 */
final readonly class WizardPaymentInitiator
{
    public function __construct(
        private PaymentProviderRegistryInterface $providers,
        private WizardSubmissionRepositoryInterface $submissions,
        private EventBusInterface $eventBus,
    ) {
    }

    /**
     * @throws \RuntimeException when payment initiation fails.
     */
    public function startPayment(WizardFormInterface $wizard, WizardSubmissionInterface $submission): string
    {
        $alias = $wizard->getPaymentProvider();
        if (! $alias) {
            throw new \RuntimeException('Wizard has no payment provider configured.');
        }

        $provider = $this->providers->get($alias);
        $paymentUrl = $provider->startPayment($submission);

        if (! is_string($paymentUrl) || trim($paymentUrl) === '') {
            throw new \RuntimeException(sprintf('Payment provider "%s" returned an empty paymentUrl.', $alias));
        }

        $this->submissions->save($submission);
        $this->eventBus->dispatch(new WizardPaymentInitiatedEvent($wizard, $submission, $paymentUrl));

        return $paymentUrl;
    }
}
