<?php
declare(strict_types = 1);

//
//  StationV1Controller.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Controller;

use Cappuccino\Entity\Station;
use Cappuccino\Entity\StationCountry;
use Cappuccino\Entity\StationLanguage;
use Cappuccino\Entity\StationTag;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/station', methods: ['GET'])]
final class StationV1Controller extends AbstractController {
    /**
     * Returns all stations in pages.
     *
     * Each page has 100 objects.
     *
     * @param Request $request
     *
     * @return JsonResponse An array of station objects
     */
    #[Route('/all')]
    public function all(Request $request): JsonResponse {
        $page = max(1, $request->query->getInt(key: 'page'));
        $limit = 100;
        $offset = ($page - 1) * $limit;

        $stations = $this->entityManager->getRepository(className: Station::class)->findBy(criteria: [], orderBy: ['name' => 'ASC'], offset: $offset, limit: $limit);
        return new JsonResponse($stations);
    }

    /**
     * Returns all station objects for given search parameters.
     *
     * @param Request $request
     *
     * @return JsonResponse An array of station objects
     */
    #[Route('/search')]
    public function search(Request $request): JsonResponse {
        $name = $request->query->getString(key: 'name');
        $countryCode = strtoupper(string: $request->query->getString(key: 'country'));
        $languageCode = strtolower(string: $request->query->getString(key: 'language'));
        $tag = strtolower(string: $request->query->getString(key: 'tag'));
        $parameters = ['name' => $name, 'countryCode' => $countryCode, 'languageCode' => $languageCode, 'tag' => $tag];

        // Make sure at least one parameter is set.
        if (!array_filter(array: $parameters)) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $stations = $this->entityManager->getRepository(className: Station::class)->findBySearchQuery(parameters: $parameters);
        return new JsonResponse($stations);
    }

    /**
     * Returns all countrie codes with at least one station.
     *
     * Country codes are in uppercase.
     *
     * @return JsonResponse An array of country codes
     */
    #[Route('/countries')]
    public function countries(): JsonResponse {
        $stationCountries = $this->entityManager->getRepository(className: StationCountry::class)->findBy(criteria: [], orderBy: ['iso31662' => 'ASC']);
        return new JsonResponse($stationCountries);
    }

    /**
     * Returns all language codes with at least one station.
     *
     * Language codes are in lowercase.
     *
     * @return JsonResponse An array of language codes
     */
    #[Route('/languages')]
    public function languages(): JsonResponse {
        $stationLanguages = $this->entityManager->getRepository(className: StationLanguage::class)->findBy(criteria: [], orderBy: ['iso6391' => 'ASC']);
        return new JsonResponse($stationLanguages);
    }

    /**
     * Returns all tags.
     *
     * Tags are in lowercase.
     *
     * @return JsonResponse An array of tags
     */
    #[Route('/tags')]
    public function tags(): JsonResponse {
        $stationTags = $this->entityManager->getRepository(className: StationTag::class)->findBy(criteria: [], orderBy: ['name' => 'ASC']);
        return new JsonResponse($stationTags);
    }
}
