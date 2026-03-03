<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentMode;

final class PaymentModeTest extends TestCase
{
    public function testItHasExpectedBackedValues(): void
    {
        self::assertSame('none', PaymentMode::None->value);
        self::assertSame('afterward', PaymentMode::Afterward->value);
        self::assertSame('required', PaymentMode::Required->value);
    }

    public function testTryFrom(): void
    {
        self::assertSame(PaymentMode::None, PaymentMode::tryFrom('none'));
        self::assertNull(PaymentMode::tryFrom('unknown'));
    }
}
