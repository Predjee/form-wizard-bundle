<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Symfony\Component\Messenger\MessageBusInterface;
use Yiggle\FormWizardBundle\Application\Contract\EventBusInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardCompletionInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardSubmissionRepositoryInterface;
use Yiggle\FormWizardBundle\Application\Data\WizardFlowData;
use Yiggle\FormWizardBundle\Application\Event\WizardPaymentFailedEvent;
use Yiggle\FormWizardBundle\Application\Event\WizardSubmissionCompletedEvent;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentStatus;
use Yiggle\FormWizardBundle\Message\ProcessSubmission;

final readonly class WizardManager implements WizardCompletionInterface
{
    public function __construct(
        private WizardSubmissionCreator $submissionCreator,
        private WizardPaymentInitiator $paymentInitiator,
        private WizardSubmissionRepositoryInterface $submissionRepository,
        private MessageBusInterface $bus,
        private EventBusInterface $eventDispatcher,
    ) {
    }

    public function complete(WizardFormInterface $wizard, WizardFlowData $data, string $currency): ?string
    {
        return $this->processCompletion($wizard, $data, $currency);
    }

    public function processCompletion(WizardFormInterface $wizard, WizardFlowData $data, string $currency): ?string
    {
        $result = $this->submissionCreator->create($wizard, $data, $currency);
        $submission = $result['submission'];
        $noPaymentRequired = $result['noPaymentRequired'];

        if ($noPaymentRequired) {
            $this->eventDispatcher->dispatch(new WizardSubmissionCompletedEvent($wizard, $submission));
            $this->bus->dispatch(new ProcessSubmission($submission->getUuid()));

            return null;
        }

        try {
            return $this->paymentInitiator->startPayment($wizard, $submission);
        } catch (\Throwable $e) {
            $submission->setStatus(PaymentStatus::Failed);
            $this->submissionRepository->save($submission);

            $this->eventDispatcher->dispatch(new WizardPaymentFailedEvent($wizard, $submission, $e));

            return null;
        }
    }
}
