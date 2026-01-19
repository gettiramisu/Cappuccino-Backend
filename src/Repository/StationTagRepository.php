<?php
declare(strict_types = 1);

//
//  StationTagRepository.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Repository;

use Cappuccino\Entity\StationTag;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class StationTagRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct(registry: $registry, entityClass: StationTag::class);
    }

    /**
     * Returns all station tag objects with their name as the key.
     *
     * @return array An array of station tag objects
     */
    public function findAllKeyedByName(): array {
        $stationTags = $this->findAll();
        return array_combine(
            keys: array_map(callback: fn(StationTag $stationTag) => $stationTag->getName(), array: $stationTags),
            values: $stationTags
        );
    }
}
