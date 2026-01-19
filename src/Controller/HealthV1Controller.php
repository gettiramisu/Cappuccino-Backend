<?php
declare(strict_types = 1);

//
//  HealthV1Controller.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\DBAL\Exception as DbalException;

#[Route('/api/v1', methods: ['GET'])]
final class HealthV1Controller extends AbstractController {
    /**
     * Returns health information about the system.
     *
     * The database connection is tested.
     *
     * @return JsonResponse An array containing health information
     */
    #[Route('/health')]
    public function health(): JsonResponse {
        try {
            // Test if the connection to the database itself is healthy.
            // SELECT 1 is the fastest way of doing so.
            $this->entityManager->getConnection()->executeQuery(sql: 'SELECT 1');
            $databaseHealthy = true;
        } catch (DbalException) {
            $databaseHealthy = false;
        }

        $healthy = $databaseHealthy;

        return new JsonResponse([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => [
                'database' => $databaseHealthy
            ]
        ], $healthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
