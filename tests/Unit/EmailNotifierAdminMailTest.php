<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Infrastructure\Notification\EmailNotifier;

final class EmailNotifierAdminMailTest extends TestCase
{
    public function testItSendsAdminMailWhenReceiversContainValidEmail(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->with(self::isInstanceOf(TemplatedEmail::class));

        $notifier = new EmailNotifier($mailer, 'noreply@example.com', 'No Reply');

        $wizard = $this->createStub(WizardFormInterface::class);
        $wizard->method('isDisableAdminMails')->willReturn(false);
        $wizard->method('isDisableCustomerMails')->willReturn(true);
        $wizard->method('getReceivers')->willReturn([
            [
                'type' => 'receiver',
                'email' => 'admin@example.com',
                'name' => 'Admin',
                'receiverType' => 'to',
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
