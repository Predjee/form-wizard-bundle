<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Yiggle\FormWizardBundle\Application\Export\SubmissionFlattener;

final class SubmissionFlattenerTest extends TestCase
{
    public function testItFlattensNestedArrays(): void
    {
        $f = new SubmissionFlattener();

        $rows = $f->flattenToRows([
            'step-1' => [
                'email' => 'a@b.com',
                'name' => 'A',
            ],
            'step-2' => [
                'age' => 10,
            ],
        ]);

        self::assertCount(1, $rows);
        self::assertSame('a@b.com', $rows[0]['step-1.email'] ?? null);
        self::assertSame('A', $rows[0]['step-1.name'] ?? null);
        self::assertSame('10', $rows[0]['step-2.age'] ?? null);
    }
}
