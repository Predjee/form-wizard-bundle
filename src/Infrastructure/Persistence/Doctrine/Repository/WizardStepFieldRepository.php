<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepFieldInterface;
use Yiggle\FormWizardBundle\Entity\WizardStepField;

/**
 * @extends ServiceEntityRepository<WizardStepField>
 */
final class WizardStepFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WizardStepField::class);
    }

    /**
     * @param mixed $id
     * @param \Doctrine\DBAL\LockMode|int|null $lockMode
     * @param int|null $lockVersion
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?WizardStepFieldInterface
    {
        /** @var WizardStepField|null $entity */
        $entity = parent::find($id, $lockMode, $lockVersion);

        return $entity;
    }

    public function save(WizardStepFieldInterface $field): void
    {
        if (! $field instanceof WizardStepField) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', WizardStepField::class, $field::class));
        }

        $this->getEntityManager()->persist($field);
        $this->getEntityManager()->flush();
    }
}
