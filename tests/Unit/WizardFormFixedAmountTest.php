<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Yiggle\FormWizardBundle\Domain\Entity\WizardForm;

final class WizardFormFixedAmountTest extends TestCase
{
    public function testItNormalizesCommaDecimal(): void
    {
        $f = new WizardForm('w');
        $f->setTitle('T');

        $f->setFixedAmount('10,5');
        self::assertSame('10.50', $f->getFixedAmount());
    }

    public function testItClearsOnEmpty(): void
    {
        $f = new WizardForm('w');
        $f->setTitle('T');

        $f->setFixedAmount('12');
        self::assertSame('12.00', $f->getFixedAmount());

        $f->setFixedAmount('');
        self::assertNull($f->getFixedAmount());
    }

    public function testItRejectsNonNumeric(): void
    {
        $f = new WizardForm('w');
        $f->setTitle('T');

        $this->expectException(\InvalidArgumentException::class);
        $f->setFixedAmount('abc');
    }
}
