<?php
declare(strict_types = 1);

//
//  StationRepository.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Repository;

use Cappuccino\Entity\Station;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class StationRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct(registry: $registry, entityClass: Station::class);
    }

    /**
     * Returns all station objects with their uuid as the key.
     *
     * @return array An array of station objects
     */
    public function findAllKeyedByUuid(): array {
        $stations = $this->findAll();
        return array_combine(
            keys: array_map(callback: fn(Station $station) => $station->getUuid(), array: $stations),
            values: $stations
        );
    }

    /**
     * Returns station objects for given search parameters
     *
     * @param array $parameters The search parameters
     *
     * @return array An array of station objects
     */
    public function findBySearchQuery(array $parameters): array {
        $queryBuilder = $this->createQueryBuilder(alias: 's');
        $queryBuilder->setMaxResults(100)
                     ->orderBy(sort: 's.name');

        if ($parameters['name']) {
            $queryBuilder->andWhere('s.name LIKE :name')
                         ->setParameter(key: 'name', value: "%{$parameters['name']}%");
        }

        if ($parameters['countryCode']) {
            $queryBuilder->join(join: 's.stationCountry', alias: 'sc')
                         ->andWhere('sc.iso31662 = :iso31662')
                         ->setParameter(key: 'iso31662', value: $parameters['countryCode']);
        }

        if ($parameters['languageCode']) {
            $queryBuilder->join(join: 's.stationLanguages', alias: 'sl')
                         ->andWhere('sl.iso6391 = :iso6391')
                         ->setParameter(key: 'iso6391', value: $parameters['languageCode']);
        }

        if ($parameters['tag']) {
            $queryBuilder->join(join: 's.stationTags', alias: 'st')
                         ->andWhere('st.name = :tag')
                         ->setParameter(key: 'tag', value: $parameters['tag']);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
