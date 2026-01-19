<?php
declare(strict_types = 1);

//
//  HealthV1ControllerTest.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Tests\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Response;

use Cappuccino\Controller\HealthV1Controller;

class HealthV1ControllerTest extends AbstractTestController {
    /**
     * Tests the setters and getters.
     */
    public function testSettersAndGetters(): void {
        $controller = static::getContainer()->get(id: HealthV1Controller::class);

        $entityManager = $this->createStub(type: EntityManagerInterface::class);
        $controller->setEntityManager(entityManager: $entityManager);
        $this->assertSame(expected: $entityManager, actual: $controller->getEntityManager());
    }

    /**
     * Tests a healthy database connection.
     */
    public function testHealthy(): void {
        $crawler = $this->client->request(method: 'GET', uri: '/api/v1/health');

        $this->assertSame(expected: Response::HTTP_OK, actual: $this->client->getResponse()->getStatusCode());

        $responseData = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertSame(expected: 'healthy', actual: $responseData['status']);
        $this->assertSame(expected: ['database' => true], actual: $responseData['checks']);
    }

    /**
     * Tests an unhealthy database connection.
     *
     * The database connection is mocked to throw an exception.
     */
    public function testUnhealthyDatabase(): void {
        $mockDbalException = $this->createStub(type: DbalException::class);

        $mockConnection = $this->createMock(type: Connection::class);
        $mockConnection->expects($this->once())
                       ->method(constraint: 'executeQuery')
                       ->with('SELECT 1')
                       ->willThrowException(exception: $mockDbalException);

        $mockEntityManager = $this->createMock(type: EntityManagerInterface::class);
        $mockEntityManager->expects($this->once())
                          ->method(constraint: 'getConnection')
                          ->willReturn(value: $mockConnection);

        $controller = static::getContainer()->get(id: HealthV1Controller::class);
        $controller->setEntityManager(entityManager: $mockEntityManager);

        $this->client->request(method: 'GET', uri: '/api/v1/health');

        $this->assertSame(expected: Response::HTTP_SERVICE_UNAVAILABLE, actual: $this->client->getResponse()->getStatusCode());

        $responseData = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertSame(expected: 'unhealthy', actual: $responseData['status']);
        $this->assertSame(expected: ['database' => false], actual: $responseData['checks']);
    }
}
