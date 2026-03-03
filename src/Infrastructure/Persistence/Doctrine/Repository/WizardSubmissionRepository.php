<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Yiggle\FormWizardBundle\Application\Contract\WizardSubmissionRepositoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Entity\WizardSubmission;

/**
 * @extends ServiceEntityRepository<WizardSubmission>
 */
final class WizardSubmissionRepository extends ServiceEntityRepository implements WizardSubmissionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WizardSubmission::class);
    }

    public function save(WizardSubmissionInterface $submission, bool $flush = true): void
    {
        if (! $submission instanceof WizardSubmission) {
            throw new \InvalidArgumentException('Expected concrete WizardSubmission entity.');
        }

        $this->getEntityManager()->persist($submission);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUuid(string $uuid): ?WizardSubmissionInterface
    {
        /** @var WizardSubmission|null $found */
        $found = $this->find($uuid);

        return $found;
    }

    public function findOneByPaymentReference(string $paymentReference): ?WizardSubmissionInterface
    {
        /** @var WizardSubmission|null $found */
        $found = $this->findOneBy([
            'paymentReference' => $paymentReference,
        ]);

        return $found;
    }

    public function iterateByFormUuid(string $formUuid): iterable
    {
        return $this->createQueryBuilder('s')
            ->andWhere('IDENTITY(s.form) = :formUuid')
            ->setParameter('formUuid', $formUuid)
            ->orderBy('s.createdAt', 'ASC')
            ->getQuery()
            ->toIterable();
    }
}
