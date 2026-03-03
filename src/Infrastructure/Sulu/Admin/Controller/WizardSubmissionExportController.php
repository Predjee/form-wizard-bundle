<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Controller;

use Http\Discovery\Exception\NotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Yiggle\FormWizardBundle\Application\Export\SubmissionCsvExporter;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository\WizardFormRepository;
use Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository\WizardSubmissionRepository;

#[Route('/admin/api/fw/forms/{id}/submissions.csv', name: 'fw_admin_export_submissions_csv', methods: ['GET'])]
final class WizardSubmissionExportController extends AbstractController
{
    public function __construct(
        private readonly WizardFormRepository $forms,
        private readonly WizardSubmissionRepository $submissions,
        private readonly SubmissionCsvExporter $exporter,
    ) {
    }

    public function __invoke(string $id): StreamedResponse
    {
        $form = $this->forms->find($id) ?? throw new NotFoundException();

        $submissions = $this->materializeSubmissions($form->getUuid());

        $headers = $this->exporter->buildHeaders($submissions);

        $filename = sprintf('wizard_%s_submissions.csv', $form->getUuid());

        $response = new StreamedResponse(function () use ($headers, $submissions): void {
            $out = fopen('php://output', 'wb');

            if (! is_resource($out)) {
                return;
            }

            $delimiter = ';';

            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, $headers, $delimiter);

            foreach ($submissions as $submission) {
                foreach ($this->exporter->rowsForSubmission($submission) as $row) {
                    $line = [];
                    foreach ($headers as $h) {
                        $line[] = $row[$h] ?? '';
                    }
                    fputcsv($out, $line, $delimiter);
                }
            }

            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * @return list<WizardSubmissionInterface>
     */
    private function materializeSubmissions(string $formUuid): array
    {
        $iterable = $this->iterateSubmissions($formUuid);

        if (is_array($iterable)) {
            return array_values($iterable);
        }

        return iterator_to_array($iterable, false);
    }

    /**
     * @return iterable<WizardSubmissionInterface>
     */
    private function iterateSubmissions(string $formUuid): iterable
    {
        return $this->submissions->iterateByFormUuid($formUuid);
    }
}
