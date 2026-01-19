<?php
declare(strict_types = 1);

//
//  AbstractController.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;

// TODO: implement paging on the search page
// TODO: check the new sync command (performance n stuff)
// TODO: add logging
// TODO: finish the tag mapper
// TODO: create a docker image

abstract class AbstractController {
    protected EntityManagerInterface $entityManager;
    protected Logger $logger;

    /**
     * @internal
     *
     * @param EntityManagerInterface $entityManager
     *
     * @return static
     */
    public function setEntityManager(EntityManagerInterface $entityManager): static {
        $this->entityManager = $entityManager;
        return $this;
    }

    /**
     * @internal
     *
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface {
        return $this->entityManager;
    }

    /**
     * @internal
     *
     * @param Logger $logger
     *
     * @return static
     */
    public function setLogger(Logger $logger): static {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @internal
     *
     * @return Logger
     */
    public function getLogger(): Logger {
        return $this->logger;
    }
}
