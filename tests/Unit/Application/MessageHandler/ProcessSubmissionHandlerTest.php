<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit\Application\MessageHandler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Yiggle\FormWizardBundle\Application\Contract\NotificationDispatcherInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardSubmissionRepositoryInterface;
use Yiggle\FormWizardBundle\Application\MessageHandler\ProcessSubmissionHandler;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Message\ProcessSubmission;

#[CoversClass(ProcessSubmissionHandler::class)]
final class ProcessSubmissionHandlerTest extends TestCase
{
    public function testItDispatchesNotifications(): void
    {
        $repository = $this->createMock(WizardSubmissionRepositoryInterface::class);
        $notifications = $this->createMock(NotificationDispatcherInterface::class);

        $wizard = $this->createStub(WizardFormInterface::class);
        $submission = $this->createStub(WizardSubmissionInterface::class);

        $submission->method('getForm')->willReturn($wizard);

        $repository
            ->expects(self::once())
            ->method('findByUuid')
            ->with('sub-123')
            ->willReturn($submission);

        $notifications
            ->expects(self::once())
            ->method('dispatchNotifications')
            ->with($wizard, $submission);

        $handler = new ProcessSubmissionHandler(
            $repository,
            $notifications
        );

        $handler(new ProcessSubmission('sub-123'));
    }

    public function testItDoesNothingWhenSubmissionIsMissing(): void
    {
        $repository = $this->createMock(WizardSubmissionRepositoryInterface::class);
        $notifications = $this->createMock(NotificationDispatcherInterface::class);

        $repository
            ->expects(self::once())
            ->method('findByUuid')
            ->with('missing')
            ->willReturn(null);

        $notifications
            ->expects(self::never())
            ->method('dispatchNotifications');

        $handler = new ProcessSubmissionHandler(
            $repository,
            $notifications
        );

        $handler(new ProcessSubmission('missing'));
    }
}
