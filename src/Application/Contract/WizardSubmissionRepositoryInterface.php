<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Contract;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;

/**
 * Application-level abstraction over submission persistence.
 *
 * This prevents Application services from depending directly on Doctrine.
 */
interface WizardSubmissionRepositoryInterface
{
    public function save(WizardSubmissionInterface $submission): void;

    public function findByUuid(string $uuid): ?WizardSubmissionInterface;

    public function findOneByPaymentReference(string $paymentReference): ?WizardSubmissionInterface;

    /**
     * @return iterable<int, WizardSubmissionInterface>
     */
    public function iterateByFormUuid(string $formUuid): iterable;
}
