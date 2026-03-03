<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Yiggle\FormWizardBundle\Application\Export\SubmissionCsvExporter;
use Yiggle\FormWizardBundle\Entity\WizardForm;
use Yiggle\FormWizardBundle\Entity\WizardSubmission;

#[Route(
    path: '/_test/fw/forms/{id}/submissions.csv',
    name: 'test_fw_export_submissions_csv',
    methods: ['GET']
)]
final class TestExportController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SubmissionCsvExporter $exporter,
    ) {
    }

    public function __invoke(string $id): Response
    {
        /** @var WizardForm|null $form */
        $form = $this->em->getRepository(WizardForm::class)->findOneBy([
            'uuid' => $id,
        ]);
        if (! $form) {
            return new Response('Form not found', Response::HTTP_NOT_FOUND);
        }

        /** @var list<WizardSubmission> $submissions */
        $submissions = $this->em->getRepository(WizardSubmission::class)->findBy(
            [
                'form' => $form,
            ],
            [
                'createdAt' => 'ASC',
            ]
        );

        $headers = $this->exporter->buildHeaders($submissions);

        $fh = fopen('php://temp', 'r+');
        if ($fh === false) {
            throw new \RuntimeException('Unable to open temporary memory stream for CSV export.');
        }

        fputcsv($fh, $headers, escape: '');

        foreach ($submissions as $submission) {
            foreach ($this->exporter->rowsForSubmission($submission) as $row) {
                $line = [];
                foreach ($headers as $h) {
                    $line[] = $row[$h] ?? '';
                }
                fputcsv($fh, $line);
            }
        }

        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return new Response(
            $csv !== false ? $csv : '',
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="submissions.csv"',
            ]
        );
    }
}
