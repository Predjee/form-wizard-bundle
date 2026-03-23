<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Application\Service\FieldValueMapper;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Infrastructure\Notification\EmailNotifier;

final class EmailNotifierInvalidReceiversTest extends TestCase
{
    public function testItDoesNotSendAdminMailWhenAllReceiverEmailsAreInvalid(): void
    {
        $fieldValueMapper = new FieldValueMapper();
        $translationInterface = $this->createStub(TranslatorInterface::class);
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::never())->method('send');

        $notifier = new EmailNotifier($mailer, $fieldValueMapper, $translationInterface, 'noreply@example.com', 'No Reply');

        $wizard = $this->createStub(WizardFormInterface::class);
        $wizard->method('isDisableAdminMails')->willReturn(false);
        $wizard->method('isDisableCustomerMails')->willReturn(true);
        $wizard->method('getReceivers')->willReturn([
            [
                'type' => 'receiver',
                'email' => 'not-an-email',
                'name' => 'Bad',
                'receiverType' => 'to',
            ],
            [
                'type' => 'receiver',
                'email' => '',
                'name' => 'Empty',
                'receiverType' => 'cc',
            ],
        ]);
        $wizard->method('getSteps')->willReturn([]);
        $wizard->method('getFromEmail')->willReturn(null);
        $wizard->method('getFromName')->willReturn(null);
        $wizard->method('getSubject')->willReturn(null);
        $wizard->method('getTitle')->willReturn('Test Wizard');
        $wizard->method('getMailTextAdmin')->willReturn(null);
        $wizard->method('isIncludeFormCopyInCustomerMail')->willReturn(false);

        $submission = $this->createStub(WizardSubmissionInterface::class);
        $submission->method('getData')->willReturn([]);

        $notifier->notify($wizard, $submission);
    }
}
