<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Yiggle\FormWizardBundle\Application\Export\SubmissionCsvExporter;
use Yiggle\FormWizardBundle\Application\Export\SubmissionFlattener;
use Yiggle\FormWizardBundle\Domain\Entity\WizardForm;
use Yiggle\FormWizardBundle\Domain\Entity\WizardSubmission;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentStatus;

final class SubmissionCsvExporterTest extends TestCase
{
    public function testItBuildsHeadersAndRows(): void
    {
        $exporter = new SubmissionCsvExporter(new SubmissionFlattener());

        $wizard = new WizardForm('w');
        $wizard->setTitle('T');

        $s = new WizardSubmission('s1');
        $s->setForm($wizard);
        $s->setStatus(PaymentStatus::Completed);
        $s->setCurrency('EUR');
        $s->setTotalAmountCents(123);
        $s->setData([
            'step-1' => [
                'email' => 'a@b.com',
                'name' => 'Test',
            ],
        ]);

        $headers = $exporter->buildHeaders([$s]);

        self::assertContains('submission_id', $headers);
        self::assertContains('status', $headers);
        self::assertContains('step-1.email', $headers);

        $rows = iterator_to_array($exporter->rowsForSubmission($s));
        self::assertCount(1, $rows);
        self::assertSame('a@b.com', $rows[0]['step-1.email']);
    }
}
