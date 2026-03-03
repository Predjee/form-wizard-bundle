<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\UseCase\CompleteWizard;

use Yiggle\FormWizardBundle\Application\Contract\WizardCompletionInterface;

final readonly class CompleteWizard
{
    public function __construct(
        private WizardCompletionInterface $completion,
    ) {
    }

    public function __invoke(CompleteWizardRequest $request): CompleteWizardResult
    {
        $paymentUrl = $this->completion->complete($request->wizard, $request->data, $request->currency);

        return new CompleteWizardResult($request->wizard->getUuid(), $paymentUrl);
    }
}
