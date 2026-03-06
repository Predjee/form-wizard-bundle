<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Yiggle\FormWizardBundle\Application\Export\SubmissionCsvExporter;
use Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository\WizardFormRepository;
use Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository\WizardSubmissionRepository;

#[Route('/admin/api/fw/forms/{id}/submissions.csv', name: 'fw_admin_export_submissions_csv', methods: ['GET'])]
final class WizardSubmissionExportController extends AbstractController
{
    public function __construct(
        private WizardFormRepository $forms,
        private WizardSubmissionRepository $submissions,
        private SubmissionCsvExporter $exporter,
    ) {
    }

    public function __invoke(string $id): StreamedResponse
    {
        $form = $this->forms->find($id) ?? throw new NotFoundHttpException();
        $submissions = $this->submissions->iterateByFormUuid($form->getUuid());
        $headers = $this->exporter->buildHeaders($form);

        $response = new StreamedResponse(function () use ($headers, $submissions, $form): void {
            $handle = fopen('php://output', 'wb');
            if (! is_resource($handle)) {
                throw new \RuntimeException('Unable to open output stream for CSV export.');
            }

            fwrite($handle, "\xEF\xBB\xBF");

            $delimiter = ';';
            fputcsv($handle, $headers, $delimiter);

            foreach ($submissions as $submission) {
                $rows = $this->exporter->rowsForSubmission($form, $submission);
                foreach ($rows as $row) {
                    $line = [];
                    foreach ($headers as $header) {
                        $line[] = $row[$header] ?? '';
                    }
                    fputcsv($handle, $line, $delimiter);
                }
            }
            fclose($handle);
        });

        $filename = sprintf('export_%s_%s.csv', $form->getUuid(), date('Ymd'));
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
