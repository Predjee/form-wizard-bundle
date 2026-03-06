<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Yiggle\FormWizardBundle\Application\Contract\EventBusInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardSubmissionCreatorInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardSubmissionRepositoryInterface;
use Yiggle\FormWizardBundle\Application\Data\WizardFlowData;
use Yiggle\FormWizardBundle\Application\Event\WizardSubmissionCreatedEvent;
use Yiggle\FormWizardBundle\Application\Security\ReturnUrlServiceInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardSubmissionFactoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentMode;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentStatus;

/**
 * @internal This service orchestrates creation of wizard submissions and is considered
 *           an internal implementation detail of the bundle.
 */
final readonly class WizardSubmissionCreator implements WizardSubmissionCreatorInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private ReturnUrlServiceInterface $returnUrlService,
        private PriceCalculatorInterface $priceCalculator,
        private WizardSubmissionRepositoryInterface $submissions,
        private WizardSubmissionFactoryInterface $factory,
        private EventBusInterface $eventBus,
    ) {
    }

    /**
     * @return array{submission: WizardSubmissionInterface, noPaymentRequired: bool}
     */
    public function create(WizardFormInterface $wizard, WizardFlowData $data, string $currency): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $receipt = $this->priceCalculator->getReceipt($wizard, $data->steps);

        $submission = $this->factory->createNew();

        $submission->setForm($wizard);
        $submission->setData($data->steps);
        $submission->setTotalAmountCents($receipt->getTotalCents());
        $submission->setCurrency($currency);
        $submission->setProvider($wizard->getPaymentProvider());

        if ($request instanceof Request) {
            $returnTo = $this->resolveReturnTo($request, $wizard->getUuid());
            if ($returnTo) {
                $submission->setReturnUrlSigned($returnTo);
            }
        }

        $noPaymentRequired = $wizard->getPaymentMode() === PaymentMode::None || $receipt->getTotalCents() <= 0;
        $submission->setStatus($noPaymentRequired ? PaymentStatus::Completed : PaymentStatus::Pending);

        $this->submissions->save($submission);
        $this->eventBus->dispatch(new WizardSubmissionCreatedEvent($wizard, $submission, $data->steps));

        return [
            'submission' => $submission,
            'noPaymentRequired' => $noPaymentRequired,
        ];
    }

    private function resolveReturnTo(Request $request, string $wizardUuid): ?string
    {
        try {
            $fromSession = $request->getSession()->get('fw_return_to_' . $wizardUuid);
            if (\is_string($fromSession) && $fromSession !== '') {
                return $fromSession;
            }
        } catch (\Throwable) {
        }

        return $this->returnUrlService->resolveReturnTo($request);
    }
}
