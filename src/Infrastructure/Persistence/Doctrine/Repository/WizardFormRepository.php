<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Yiggle\FormWizardBundle\Application\Contract\WizardFormRepositoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Entity\WizardForm;

/**
 * @internal
 * @extends ServiceEntityRepository<WizardForm>
 */
final class WizardFormRepository extends ServiceEntityRepository implements WizardFormRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WizardForm::class);
    }

    public function findByUuid(string $uuid): ?WizardFormInterface
    {
        /** @var WizardForm|null $found */
        $found = $this->find($uuid);

        return $found;
    }

    public function save(WizardFormInterface $wizardForm, bool $flush = true): void
    {
        if (! $wizardForm instanceof WizardForm) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', WizardForm::class, $wizardForm::class));
        }

        $this->getEntityManager()->persist($wizardForm);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findWithStructure(string $uuid): ?WizardFormInterface
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        /** @var WizardForm|null $form */
        $form = $qb
            ->select('f', 's', 'sf', 'field')
            ->from(WizardForm::class, 'f')
            ->leftJoin('f.steps', 's')
            ->leftJoin('s.stepFields', 'sf')
            ->leftJoin('sf.field', 'field')
            ->where('f.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult();

        return $form;
    }
}
