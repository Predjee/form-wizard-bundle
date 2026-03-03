<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Functional;

use Yiggle\FormWizardBundle\Application\Data\WizardFlowData;
use Yiggle\FormWizardBundle\Application\Service\WizardSubmissionCreator;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentMode;
use Yiggle\FormWizardBundle\Entity\WizardForm;
use Yiggle\FormWizardBundle\Entity\WizardSubmission;

final class WizardSubmissionCreatorFunctionalTest extends FunctionalTestCase
{
    public function testItCreatesAndPersistsSubmission(): void
    {
        $wizard = new WizardForm('wizard-1');
        $wizard->setTitle('Test');
        $wizard->setPaymentMode(PaymentMode::None);

        $this->em->persist($wizard);
        $this->em->flush();

        $data = new WizardFlowData();
        $data->steps = [
            'step-1' => [
                'email' => 'customer@example.com',
            ],
        ];

        /** @var WizardSubmissionCreator $creator */
        $creator = static::getContainer()->get(WizardSubmissionCreator::class);

        $result = $creator->create($wizard, $data, 'EUR');

        self::assertArrayHasKey('submission', $result);
        self::assertArrayHasKey('noPaymentRequired', $result);

        $uuid = $result['submission']->getUuid();

        $this->em->clear();
        $saved = $this->em->find(WizardSubmission::class, $uuid);

        self::assertNotNull($saved);
        self::assertSame('EUR', $saved->getCurrency());
    }
}
