<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardSubmissionRepositoryInterface;
use Yiggle\FormWizardBundle\Application\Event\WizardSubmissionCompletedEvent;
use Yiggle\FormWizardBundle\Application\Payment\PaymentProviderRegistryInterface;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentStatus;
use Yiggle\FormWizardBundle\Message\ProcessSubmission;

final readonly class PaymentStatusProcessor
{
    public function __construct(
        private WizardSubmissionRepositoryInterface $submissions,
        private PaymentProviderRegistryInterface $registry,
        private EventDispatcherInterface $events,
        private MessageBusInterface $bus,
    ) {
    }

    public function processByTransactionId(string $providerAlias, string $transactionId): ?PaymentStatus
    {
        if ($transactionId === '') {
            return null;
        }

        $submission = $this->submissions->findOneByPaymentReference($transactionId);
        if (! $submission) {
            return null;
        }

        $provider = $this->registry->get($providerAlias);
        $newStatus = $provider->fetchStatus($transactionId);

        $oldStatus = $submission->getStatus();

        if ($oldStatus !== $newStatus) {
            $submission->setStatus($newStatus);
            $this->submissions->save($submission);
        }

        if ($oldStatus !== PaymentStatus::Completed && $newStatus === PaymentStatus::Completed) {
            $wizard = $submission->getForm();
            $this->events->dispatch(new WizardSubmissionCompletedEvent($wizard, $submission));
            $this->bus->dispatch(new ProcessSubmission($submission->getUuid()));
        }

        return $newStatus;
    }

    public function processBySubmissionUuid(string $uuid): ?PaymentStatus
    {
        $submission = $this->submissions->findByUuid($uuid);
        if (! $submission) {
            return null;
        }

        $transactionId = $submission->getPaymentReference();
        $provider = $submission->getProvider();

        if (! $transactionId || ! $provider) {
            return $submission->getStatus();
        }

        return $this->processByTransactionId($provider, $transactionId);
    }
}
