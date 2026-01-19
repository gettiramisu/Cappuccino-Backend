<?php
declare(strict_types = 1);

//
//  StationCountryRepository.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Repository;

use Cappuccino\Entity\StationCountry;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class StationCountryRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct(registry: $registry, entityClass: StationCountry::class);
    }

    /**
     * Returns all station country objects with their code as the key.
     *
     * @return array An array of station country objects
     */
    public function findAllKeyedByCode(): array {
        $stationCountries = $this->findAll();
        return array_combine(
            keys: array_map(callback: fn(StationCountry $stationCountry) => $stationCountry->getCode(), array: $stationCountries),
            values: $stationCountries
        );
    }
}
