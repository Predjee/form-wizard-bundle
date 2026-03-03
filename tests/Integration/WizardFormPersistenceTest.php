<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Integration;

use Yiggle\FormWizardBundle\Entity\WizardForm;

final class WizardFormPersistenceTest extends IntegrationTestCase
{
    public function testItPersistsAndFindsWizardForm(): void
    {
        $f = new WizardForm('w1');
        $f->setTitle('Persisted');

        $this->em->persist($f);
        $this->em->flush();
        $this->em->clear();

        $found = $this->em->find(WizardForm::class, 'w1');
        self::assertNotNull($found);
        self::assertSame('Persisted', $found->getTitle());
    }
}
