<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Yiggle\FormWizardBundle\Tests\Kernel\MinimalTestKernel;

abstract class FunctionalTestCase extends WebTestCase
{
    protected EntityManagerInterface $em;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        parent::setUp();

        $this->client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->em = $em;

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        $this->em->close();
        parent::tearDown();
        static::ensureKernelShutdown();

        while (true) {
            $handler = set_exception_handler(null);
            restore_exception_handler();
            if ($handler === null) {
                break;
            }
            restore_exception_handler();
        }
    }

    protected static function getKernelClass(): string
    {
        return MinimalTestKernel::class;
    }

    private function createSchema(): void
    {
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($this->em);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }
}
