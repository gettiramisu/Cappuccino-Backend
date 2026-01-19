<?php
declare(strict_types = 1);

//
//  StationLanguageRepository.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Repository;

use Cappuccino\Entity\StationLanguage;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class StationLanguageRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct(registry: $registry, entityClass: StationLanguage::class);
    }

    /**
     * Returns all station language objects with their code as the key.
     *
     * @return array An array of station language objects
     */
    public function findAllKeyedByCode(): array {
        $stationLanguages = $this->findAll();
        return array_combine(
            keys: array_map(callback: fn(StationLanguage $stationLanguage) => $stationLanguage->getCode(), array: $stationLanguages),
            values: $stationLanguages
        );
    }
}
