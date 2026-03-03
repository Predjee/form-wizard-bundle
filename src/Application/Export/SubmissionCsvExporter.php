<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Export;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;

final readonly class SubmissionCsvExporter
{
    public function __construct(
        private SubmissionFlattener $flattener,
    ) {
    }

    /**
     * @param iterable<WizardSubmissionInterface> $submissions
     * @return array<string>
     */
    public function buildHeaders(iterable $submissions): array
    {
        $base = [
            'submission_id',
            'submitted_at',
            'status',
            'currency',
            'total_amount_cents',
        ];

        $keys = [];
        foreach ($submissions as $submission) {
            foreach ($this->rowsForSubmission($submission) as $row) {
                foreach (array_keys($row) as $k) {
                    $keys[$k] = true;
                }
            }
        }

        foreach ($base as $k) {
            unset($keys[$k]);
        }

        $dynamic = array_keys($keys);
        sort($dynamic, SORT_STRING);

        return array_merge($base, $dynamic);
    }

    /**
     * @return iterable<array<string, string>>
     */
    public function rowsForSubmission(WizardSubmissionInterface $submission): iterable
    {
        $base = [
            'submission_id' => $submission->getUuid(),
            'submitted_at' => $submission->getCreatedAt()->format('Y-m-d H:i:s'),
            'status' => $submission->getStatus()->value,
            'currency' => $submission->getCurrency(),
            'total_amount_cents' => (string) $submission->getTotalAmountCents(),
        ];

        $data = $submission->getData();
        $rows = $this->flattener->flattenToRows($data);

        foreach ($rows as $r) {
            yield $base + $this->stringifyRow($r);
        }
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, string>
     */
    private function stringifyRow(array $row): array
    {
        return array_map(fn ($v): string => $v === null ? '' : (string) $v, $row);
    }
}
