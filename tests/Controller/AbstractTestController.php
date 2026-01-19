<?php
declare(strict_types = 1);

//
//  AbstractTestController.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractTestController extends WebTestCase {
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    /**
     * Creates the client and entity manager before each test run.
     */
    protected function setUp(): void {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(id: EntityManagerInterface::class);
    }

    /**
     * Creates the database schema for given entity classes.
     *
     * The old schema are dropped.
     *
     * @param array $classes The entity classes
     */
    protected function createDatabase(array $classes): void {
        $metadata = [];
        foreach ($classes as $class) {
            $metadata[] = $this->entityManager->getClassMetadata(className: $class);
        }

        $schemaTool = new SchemaTool(em: $this->entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
