<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Yiggle\FormWizardBundle\Application\View\WizardRenderVariant;

final class WizardRenderVariantTest extends TestCase
{
    public function testTryFromWorks(): void
    {
        self::assertSame(WizardRenderVariant::Card, WizardRenderVariant::tryFrom('card'));
        self::assertNull(WizardRenderVariant::tryFrom('does-not-exist'));
    }
}
