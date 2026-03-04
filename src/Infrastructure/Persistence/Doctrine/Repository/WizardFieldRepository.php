<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;
use Yiggle\FormWizardBundle\Domain\Entity\WizardField;

/**
 * @extends ServiceEntityRepository<WizardField>
 */
final class WizardFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WizardField::class);
    }

    public function save(WizardFieldInterface $field, bool $flush = true): void
    {
        if (! $field instanceof WizardField) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', WizardField::class, $field::class));
        }

        $this->getEntityManager()->persist($field);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
