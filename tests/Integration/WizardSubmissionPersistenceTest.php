<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Integration;

use Yiggle\FormWizardBundle\Entity\WizardForm;
use Yiggle\FormWizardBundle\Entity\WizardSubmission;

final class WizardSubmissionPersistenceTest extends IntegrationTestCase
{
    public function testItPersistsSubmissionAndLinksToForm(): void
    {
        $form = new WizardForm('f1');
        $form->setTitle('Form');

        $sub = new WizardSubmission('s1');
        $sub->setForm($form);
        $sub->setData([
            'step-1' => [
                'email' => 'x@y.com',
            ],
        ]);

        $this->em->persist($form);
        $this->em->persist($sub);
        $this->em->flush();
        $this->em->clear();

        $found = $this->em->find(WizardSubmission::class, 's1');
        self::assertNotNull($found);
        self::assertSame('f1', $found->getForm()->getUuid());
    }
}
