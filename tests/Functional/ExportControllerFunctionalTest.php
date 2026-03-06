<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;
use Yiggle\FormWizardBundle\Domain\Entity\WizardForm;

final class ExportControllerFunctionalTest extends FunctionalTestCase
{
    public function testExportReturnsCsvResponse(): void
    {
        $em = $this->em;

        $form = new WizardForm();
        $form->setTitle('Test form');
        $em->persist($form);
        $em->flush();

        $uuid = $form->getUuid();

        $this->client->request('GET', sprintf('/_test/fw/forms/%s/submissions.csv', $uuid));

        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));

        $content = (string) $response->getContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content, 'De CSV moet beginnen met een UTF-8 BOM.');

        $this->assertStringContainsString('"Submission Date"', $content);
        $this->assertStringContainsString('"Total Paid"', $content);
    }
}
