<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Functional;

use Yiggle\FormWizardBundle\Application\Export\SubmissionCsvExporter;

final class ContainerBootFunctionalTest extends FunctionalTestCase
{
    public function testContainerCompilesAndCoreServicesExist(): void
    {
        $c = static::getContainer();

        self::assertTrue($c->has('doctrine.orm.entity_manager'));
        self::assertTrue($c->has(SubmissionCsvExporter::class));
    }
}
