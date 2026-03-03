<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;
use Yiggle\FormWizardBundle\Entity\WizardStep;

/**
 * @extends ServiceEntityRepository<WizardStep>
 */
final class WizardStepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WizardStep::class);
    }

    public function save(WizardStepInterface $step, bool $flush = true): void
    {
        if (! $step instanceof WizardStep) {
            throw new \InvalidArgumentException('Invalid entity: expected ' . WizardStep::class);
        }

        $this->getEntityManager()->persist($step);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
