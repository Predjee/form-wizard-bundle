<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;
use Yiggle\FormWizardBundle\Entity\WizardForm;

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
        self::assertStringContainsString('submission_id', $content);
    }
}
