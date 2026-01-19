<?php
declare(strict_types = 1);

//
//  StationV1ControllerTest.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Tests\Controller;

use Cappuccino\Controller\StationV1Controller;
use Cappuccino\Entity\Station;
use Cappuccino\Entity\StationCountry;
use Cappuccino\Entity\StationLanguage;
use Cappuccino\Entity\StationTag;

use Doctrine\ORM\EntityManagerInterface;

use Monolog\Logger;

use Symfony\Component\HttpFoundation\Response;

class StationV1ControllerTest extends AbstractTestController {
    /**
     * Tests the setters and getters.
     */
    public function testSettersAndGetters(): void {
        $controller = static::getContainer()->get(id: StationV1Controller::class);

        $entityManager = $this->createStub(type: EntityManagerInterface::class);
        $controller->setEntityManager(entityManager: $entityManager);
        $this->assertSame(expected: $entityManager, actual: $controller->getEntityManager());

        $logger = $this->createStub(type: Logger::class);
        $controller->setLogger(logger: $logger);
        $this->assertSame(expected: $logger, actual: $controller->getLogger());
    }

    /**
     * Tests the all stations endpoint with 2 pages.
     *
     * The first page is expected to return 100 stations and the second page 1.
     */
    public function testAll(): void {
        $this->createDatabase(classes: [Station::class, StationCountry::class, StationLanguage::class, StationTag::class]);

        $country = new StationCountry();
        $country->setISO31662(iso31662: 'LU');
        $this->entityManager->persist(object: $country);

        $language = new StationLanguage();
        $language->setISO6391(iso6391: 'lb');
        $this->entityManager->persist(object: $language);

        $tagPop = new StationTag();
        $tagPop->setName(name: 'pop');
        $this->entityManager->persist(object: $tagPop);

        $tagRock = new StationTag();
        $tagRock->setName(name: 'rock');
        $this->entityManager->persist(object: $tagRock);

        for ($i = 1; $i <= 101; $i++) {
            $station = new Station();
            $station->setUuid(uuid: sprintf('%08d-0000-0000-0000-000000000000', $i));
            $station->setName(name: sprintf('Station %03d', $i));
            $station->setStreamUrl(streamUrl: sprintf('http://example.com/%d.mp3', $i));
            $station->setHomepageUrl(homepageUrl: sprintf('http://example.com/%d', $i));
            $station->setIconUrl(iconUrl: sprintf('http://example.com/%d.png', $i));
            $station->setStationCountry(stationCountry: $country);
            $station->addStationLanguage(stationLanguage: $language);
            $station->addStationTag(stationTag: $tagPop);
            $station->addStationTag(stationTag: $tagRock);
            $this->entityManager->persist(object: $station);
        }

        $this->entityManager->flush();

        $this->client->request(method: 'GET', uri: '/api/v1/station/all?page=1');
        $this->assertSame(expected: Response::HTTP_OK, actual: $this->client->getResponse()->getStatusCode());

        $page1Data = json_decode(json: $this->client->getResponse()->getContent(), associative: true);
        $this->assertCount(expectedCount: 100, haystack: $page1Data);
        $this->assertSame(expected: '00000001-0000-0000-0000-000000000000', actual: $page1Data[0]['uuid']);
        $this->assertSame(expected: 'Station 001', actual: $page1Data[0]['name']);
        $this->assertSame(expected: 'http://example.com/1.mp3', actual: $page1Data[0]['stream_url']);
        $this->assertSame(expected: 'http://example.com/1', actual: $page1Data[0]['homepage_url']);
        $this->assertSame(expected: 'http://example.com/1.png', actual: $page1Data[0]['icon_url']);
        $this->assertSame(expected: 'LU', actual: $page1Data[0]['country']['iso_3166_2']);
        $this->assertCount(expectedCount: 1, haystack: $page1Data[0]['languages']);
        $this->assertSame(expected: 'lb', actual: $page1Data[0]['languages'][0]['iso_639_1']);
        $this->assertCount(expectedCount: 2, haystack: $page1Data[0]['tags']);
        $this->assertSame(expected: 'pop', actual: $page1Data[0]['tags'][0]['name']);
        $this->assertSame(expected: 'rock', actual: $page1Data[0]['tags'][1]['name']);
        $this->assertSame(expected: 'Station 100', actual: $page1Data[99]['name']);

        $this->client->request(method: 'GET', uri: '/api/v1/station/all?page=2');
        $this->assertSame(expected: Response::HTTP_OK, actual: $this->client->getResponse()->getStatusCode());

        $page2Data = json_decode(json: $this->client->getResponse()->getContent(), associative: true);
        $this->assertCount(expectedCount: 1, haystack: $page2Data);
        $this->assertSame(expected: '00000101-0000-0000-0000-000000000000', actual: $page2Data[0]['uuid']);
        $this->assertSame(expected: 'Station 101', actual: $page2Data[0]['name']);
        $this->assertSame(expected: 'http://example.com/101.mp3', actual: $page2Data[0]['stream_url']);
        $this->assertSame(expected: 'http://example.com/101', actual: $page2Data[0]['homepage_url']);
        $this->assertSame(expected: 'http://example.com/101.png', actual: $page2Data[0]['icon_url']);
        $this->assertSame(expected: 'LU', actual: $page2Data[0]['country']['iso_3166_2']);
        $this->assertCount(expectedCount: 1, haystack: $page2Data[0]['languages']);
        $this->assertSame(expected: 'lb', actual: $page2Data[0]['languages'][0]['iso_639_1']);
        $this->assertCount(expectedCount: 2, haystack: $page2Data[0]['tags']);
        $this->assertSame(expected: 'pop', actual: $page2Data[0]['tags'][0]['name']);
        $this->assertSame(expected: 'rock', actual: $page2Data[0]['tags'][1]['name']);
    }

    /**
     * Tests that the search endpoint returns a bad request response when no parameters are provided.
     */
    public function testSearchWithoutParameters(): void {
        $this->client->request(method: 'GET', uri: '/api/v1/station/search');
        $this->assertSame(expected: Response::HTTP_BAD_REQUEST, actual: $this->client->getResponse()->getStatusCode());
    }

    /**
     * Tests that the search endpoint returns all stations that include a given name.
     */
    public function testSearchByName(): void {
        $this->createDatabase(classes: [Station::class, StationCountry::class, StationLanguage::class, StationTag::class]);

        $country = new StationCountry();
        $country->setISO31662(iso31662: 'LU');
        $this->entityManager->persist(object: $country);

        $language = new StationLanguage();
        $language->setISO6391(iso6391: 'lb');
        $this->entityManager->persist(object: $language);

        $station1 = new Station();
        $station1->setUuid(uuid: '00000001-0000-0000-0000-000000000000');
        $station1->setName(name: 'Radio Luxembourg');
        $station1->setStreamUrl(streamUrl: 'http://example.com/1.mp3');
        $station1->setHomepageUrl(homepageUrl: 'http://example.com/1');
        $station1->setIconUrl(iconUrl: 'http://example.com/1.png');
        $station1->setStationCountry(stationCountry: $country);
        $station1->addStationLanguage(stationLanguage: $language);
        $this->entityManager->persist(object: $station1);

        $station2 = new Station();
        $station2->setUuid(uuid: '00000002-0000-0000-0000-000000000000');
        $station2->setName(name: 'Radio Berlin');
        $station2->setStreamUrl(streamUrl: 'http://example.com/2.mp3');
        $station2->setHomepageUrl(homepageUrl: 'http://example.com/2');
        $station2->setIconUrl(iconUrl: 'http://example.com/2.png');
        $station2->setStationCountry(stationCountry: $country);
        $station2->addStationLanguage(stationLanguage: $language);
        $this->entityManager->persist(object: $station2);

        $station3 = new Station();
        $station3->setUuid(uuid: '00000003-0000-0000-0000-000000000000');
        $station3->setName(name: 'Pop Hits');
        $station3->setStreamUrl(streamUrl: 'http://example.com/3.mp3');
        $station3->setHomepageUrl(homepageUrl: 'http://example.com/3');
        $station3->setIconUrl(iconUrl: 'http://example.com/3.png');
        $station3->setStationCountry(stationCountry: $country);
        $station3->addStationLanguage(stationLanguage: $language);
        $this->entityManager->persist(object: $station3);

        $this->entityManager->flush();

        $this->client->request(method: 'GET', uri: '/api/v1/station/search?name=Radio');
        $this->assertSame(expected: Response::HTTP_OK, actual: $this->client->getResponse()->getStatusCode());

        $data = json_decode(json: $this->client->getResponse()->getContent(), associative: true);
        $this->assertCount(expectedCount: 2, haystack: $data);
        $this->assertSame(expected: '00000002-0000-0000-0000-000000000000', actual: $data[0]['uuid']);
        $this->assertSame(expected: 'Radio Berlin', actual: $data[0]['name']);
        $this->assertSame(expected: 'http://example.com/2.mp3', actual: $data[0]['stream_url']);
        $this->assertSame(expected: 'http://example.com/2', actual: $data[0]['homepage_url']);
        $this->assertSame(expected: 'http://example.com/2.png', actual: $data[0]['icon_url']);
        $this->assertSame(expected: 'LU', actual: $data[0]['country']['iso_3166_2']);
        $this->assertCount(expectedCount: 1, haystack: $data[0]['languages']);
        $this->assertSame(expected: 'lb', actual: $data[0]['languages'][0]['iso_639_1']);
        $this->assertCount(expectedCount: 0, haystack: $data[0]['tags']);
        $this->assertSame(expected: 'Radio Luxembourg', actual: $data[1]['name']);
    }

    /**
     * Tests that the search endpoint returns all stations that have a given country code.
     */
    public function testSearchByCountry(): void {
        $this->createDatabase(classes: [Station::class, StationCountry::class, StationLanguage::class, StationTag::class]);

        $countryLU = new StationCountry();
        $countryLU->setISO31662(iso31662: 'LU');
        $this->entityManager->persist(object: $countryLU);

        $countryDE = new StationCountry();
        $countryDE->setISO31662(iso31662: 'DE');
        $this->entityManager->persist(object: $countryDE);

        $language = new StationLanguage();
        $language->setISO6391(iso6391: 'lb');
        $this->entityManager->persist(object: $language);

        $station1 = new Station();
        $station1->setUuid(uuid: '00000001-0000-0000-0000-000000000000');
        $station1->setName(name: 'Station LU');
        $station1->setStreamUrl(streamUrl: 'http://example.com/1.mp3');
        $station1->setHomepageUrl(homepageUrl: 'http://example.com/1');
        $station1->setIconUrl(iconUrl: 'http://example.com/1.png');
        $station1->setStationCountry(stationCountry: $countryLU);
        $station1->addStationLanguage(stationLanguage: $language);
        $this->entityManager->persist(object: $station1);

        $station2 = new Station();
        $station2->setUuid(uuid: '00000002-0000-0000-0000-000000000000');
        $station2->setName(name: 'Station DE');
        $station2->setStreamUrl(streamUrl: 'http://example.com/2.mp3');
        $station2->setHomepageUrl(homepageUrl: 'http://example.com/2');
        $station2->setIconUrl(iconUrl: 'http://example.com/2.png');
        $station2->setStationCountry(stationCountry: $countryDE);
        $station2->addStationLanguage(stationLanguage: $language);
        $this->entityManager->persist(object: $station2);

        $this->entityManager->flush();

        $this->client->request(method: 'GET', uri: '/api/v1/station/search?country=LU');
        $this->assertSame(expected: Response::HTTP_OK, actual: $this->client->getResponse()->getStatusCode());

        $data = json_decode(json: $this->client->getResponse()->getContent(), associative: true);
        $this->assertCount(expectedCount: 1, haystack: $data);
        $this->assertSame(expected: '00000001-0000-0000-0000-000000000000', actual: $data[0]['uuid']);
        $this->assertSame(expected: 'Station LU', actual: $data[0]['name']);
        $this->assertSame(expected: 'http://example.com/1.mp3', actual: $data[0]['stream_url']);
        $this->assertSame(expected: 'http://example.com/1', actual: $data[0]['homepage_url']);
        $this->assertSame(expected: 'http://example.com/1.png', actual: $data[0]['icon_url']);
        $this->assertSame(expected: 'LU', actual: $data[0]['country']['iso_3166_2']);
        $this->assertCount(expectedCount: 1, haystack: $data[0]['languages']);
        $this->assertSame(expected: 'lb', actual: $data[0]['languages'][0]['iso_639_1']);
        $this->assertCount(expectedCount: 0, haystack: $data[0]['tags']);
    }

    /**
     * Tests that the search endpoint returns all stations that have a given language code.
     */
    public function testSearchByLanguage(): void {
        $this->createDatabase(classes: [Station::class, StationCountry::class, StationLanguage::class, StationTag::class]);

        $country = new StationCountry();
        $country->setISO31662(iso31662: 'LU');
        $this->entityManager->persist(object: $country);

        $languageLb = new StationLanguage();
        $languageLb->setISO6391(iso6391: 'lb');
        $this->entityManager->persist(object: $languageLb);

        $languageDe = new StationLanguage();
        $languageDe->setISO6391(iso6391: 'de');
        $this->entityManager->persist(object: $languageDe);

        $station1 = new Station();
        $station1->setUuid(uuid: '00000001-0000-0000-0000-000000000000');
        $station1->setName(name: 'Station LB');
        $station1->setStreamUrl(streamUrl: 'http://example.com/1.mp3');
        $station1->setHomepageUrl(homepageUrl: 'http://example.com/1');
        $station1->setIconUrl(iconUrl: 'http://example.com/1.png');
        $station1->setStationCountry(stationCountry: $country);
        $station1->addStationLanguage(stationLanguage: $languageLb);
        $this->entityManager->persist(object: $station1);

        $station2 = new Station();
        $station2->setUuid(uuid: '00000002-0000-0000-0000-000000000000');
        $station2->setName(name: 'Station DE');
        $station2->setStreamUrl(streamUrl: 'http://example.com/2.mp3');
        $station2->setHomepageUrl(homepageUrl: 'http://example.com/2');
        $station2->setIconUrl(iconUrl: 'http://example.com/2.png');
        $station2->setStationCountry(stationCountry: $country);
        $station2->addStationLanguage(stationLanguage: $languageDe);
        $this->entityManager->persist(object: $station2);

        $this->entityManager->flush();

        $this->client->request(method: 'GET', uri: '/api/v1/station/search?language=de');
        $this->assertSame(expected: Response::HTTP_OK, actual: $this->client->getResponse()->getStatusCode());

        $data = json_decode(json: $this->client->getResponse()->getContent(), associative: true);
        $this->assertCount(expectedCount: 1, haystack: $data);
        $this->assertSame(expected: '00000002-0000-0000-0000-000000000000', actual: $data[0]['uuid']);
        $this->assertSame(expected: 'Station DE', actual: $data[0]['name']);
        $this->assertSame(expected: 'http://example.com/2.mp3', actual: $data[0]['stream_url']);
        $this->assertSame(expected: 'http://example.com/2', actual: $data[0]['homepage_url']);
        $this->assertSame(expected: 'http://example.com/2.png', actual: $data[0]['icon_url']);
        $this->assertSame(expected: 'LU', actual: $data[0]['country']['iso_3166_2']);
        $this->assertCount(expectedCount: 1, haystack: $data[0]['languages']);
        $this->assertSame(expected: 'de', actual: $data[0]['languages'][0]['iso_639_1']);
        $this->assertCount(expectedCount: 0, haystack: $data[0]['tags']);
    }

    /**
     * Tests that the search endpoint returns all stations that have a given tag.
     */
    public function testSearchByTag(): void {
        $this->createDatabase(classes: [Station::class, StationCountry::class, StationLanguage::class, StationTag::class]);

        $country = new StationCountry();
        $country->setISO31662(iso31662: 'LU');
        $this->entityManager->persist(object: $country);

        $language = new StationLanguage();
        $language->setISO6391(iso6391: 'lb');
        $this->entityManager->persist(object: $language);

        $tagPop = new StationTag();
        $tagPop->setName(name: 'pop');
        $this->entityManager->persist(object: $tagPop);

        $tagRock = new StationTag();
        $tagRock->setName(name: 'rock');
        $this->entityManager->persist(object: $tagRock);

        $station1 = new Station();
        $station1->setUuid(uuid: '00000001-0000-0000-0000-000000000000');
        $station1->setName(name: 'Station Pop');
        $station1->setStreamUrl(streamUrl: 'http://example.com/1.mp3');
        $station1->setHomepageUrl(homepageUrl: 'http://example.com/1');
        $station1->setIconUrl(iconUrl: 'http://example.com/1.png');
        $station1->setStationCountry(stationCountry: $country);
        $station1->addStationLanguage(stationLanguage: $language);
        $station1->addStationTag(stationTag: $tagPop);
        $this->entityManager->persist(object: $station1);

        $station2 = new Station();
        $station2->setUuid(uuid: '00000002-0000-0000-0000-000000000000');
        $station2->setName(name: 'Station Rock');
        $station2->setStreamUrl(streamUrl: 'http://example.com/2.mp3');
        $station2->setHomepageUrl(homepageUrl: 'http://example.com/2');
        $station2->setIconUrl(iconUrl: 'http://example.com/2.png');
        $station2->setStationCountry(stationCountry: $country);
        $station2->addStationLanguage(stationLanguage: $language);
        $station2->addStationTag(stationTag: $tagRock);
        $this->entityManager->persist(object: $station2);

        $this->entityManager->flush();

        $this->client->request(method: 'GET', uri: '/api/v1/station/search?tag=pop');
        $this->assertSame(expected: Response::HTTP_OK, actual: $this->client->getResponse()->getStatusCode());

        $data = json_decode(json: $this->client->getResponse()->getContent(), associative: true);
        $this->assertCount(expectedCount: 1, haystack: $data);
        $this->assertSame(expected: '00000001-0000-0000-0000-000000000000', actual: $data[0]['uuid']);
        $this->assertSame(expected: 'Station Pop', actual: $data[0]['name']);
        $this->assertSame(expected: 'http://example.com/1.mp3', actual: $data[0]['stream_url']);
        $this->assertSame(expected: 'http://example.com/1', actual: $data[0]['homepage_url']);
        $this->assertSame(expected: 'http://example.com/1.png', actual: $data[0]['icon_url']);
        $this->assertSame(expected: 'LU', actual: $data[0]['country']['iso_3166_2']);
        $this->assertCount(expectedCount: 1, haystack: $data[0]['languages']);
        $this->assertSame(expected: 'lb', actual: $data[0]['languages'][0]['iso_639_1']);
        $this->assertCount(expectedCount: 1, haystack: $data[0]['tags']);
        $this->assertSame(expected: 'pop', actual: $data[0]['tags'][0]['name']);
    }

    /**
     * Tests that the countries endpoint returns all country codes from the database.
     */
    public function testCountries(): void {
        $this->createDatabase(classes: [Station::class, StationCountry::class]);

        $countryLU = new StationCountry();
        $countryLU->setISO31662(iso31662: 'LU');
        $this->entityManager->persist(object: $countryLU);

        $countryDE = new StationCountry();
        $countryDE->setISO31662(iso31662: 'DE');
        $this->entityManager->persist(object: $countryDE);

        $countryFR = new StationCountry();
        $countryFR->setISO31662(iso31662: 'FR');
        $this->entityManager->persist(object: $countryFR);

        $station1 = new Station();
        $station1->setUuid(uuid: '11111111-1111-1111-1111-111111111111');
        $station1->setName(name: 'Station LU');
        $station1->setStreamUrl(streamUrl: 'http://example.com/lu.mp3');
        $station1->setStationCountry(stationCountry: $countryLU);
        $this->entityManager->persist(object: $station1);

        $station2 = new Station();
        $station2->setUuid(uuid: '22222222-2222-2222-2222-222222222222');
        $station2->setName(name: 'Station DE');
        $station2->setStreamUrl(streamUrl: 'http://example.com/de.mp3');
        $station2->setStationCountry(stationCountry: $countryDE);
        $this->entityManager->persist(object: $station2);

        $station3 = new Station();
        $station3->setUuid(uuid: '33333333-3333-3333-3333-333333333333');
        $station3->setName(name: 'Station FR');
        $station3->setStreamUrl(streamUrl: 'http://example.com/fr.mp3');
        $station3->setStationCountry(stationCountry: $countryFR);
        $this->entityManager->persist(object: $station3);

        $this->entityManager->flush();

        $crawler = $this->client->request(method: 'GET', uri: '/api/v1/station/countries');
        $this->assertSame(expected: Response::HTTP_OK, actual: $this->client->getResponse()->getStatusCode());

        $responseData = json_decode(json: $this->client->getResponse()->getContent(), associative: true);
        $this->assertCount(expectedCount: 3, haystack: $responseData);
        $this->assertSame(expected: 'DE', actual: $responseData[0]['iso_3166_2']);
        $this->assertSame(expected: 'FR', actual: $responseData[1]['iso_3166_2']);
        $this->assertSame(expected: 'LU', actual: $responseData[2]['iso_3166_2']);
    }

    /**
     * Tests that the languages endpoint returns all languages from the database.
     */
    public function testLanguages(): void {
        $this->createDatabase(classes: [Station::class, StationCountry::class, StationLanguage::class]);

        $country = new StationCountry();
        $country->setISO31662(iso31662: 'LU');
        $this->entityManager->persist(object: $country);

        $languageLb = new StationLanguage();
        $languageLb->setISO6391(iso6391: 'lb');
        $this->entityManager->persist(object: $languageLb);

        $languageFr = new StationLanguage();
        $languageFr->setISO6391(iso6391: 'fr');
        $this->entityManager->persist(object: $languageFr);

        $languageDe = new StationLanguage();
        $languageDe->setISO6391(iso6391: 'de');
        $this->entityManager->persist(object: $languageDe);

        $station1 = new Station();
        $station1->setUuid(uuid: '11111111-1111-1111-1111-111111111111');
        $station1->setName(name: 'Station LB');
        $station1->setStreamUrl(streamUrl: 'http://example.com/lb.mp3');
        $station1->setStationCountry(stationCountry: $country);
        $station1->addStationLanguage(stationLanguage: $languageLb);
        $this->entityManager->persist(object: $station1);

        $station2 = new Station();
        $station2->setUuid(uuid: '22222222-2222-2222-2222-222222222222');
        $station2->setName(name: 'Station FR');
        $station2->setStreamUrl(streamUrl: 'http://example.com/fr.mp3');
        $station2->setStationCountry(stationCountry: $country);
        $station2->addStationLanguage(stationLanguage: $languageFr);
        $this->entityManager->persist(object: $station2);

        $station3 = new Station();
        $station3->setUuid(uuid: '33333333-3333-3333-3333-333333333333');
        $station3->setName(name: 'Station DE');
        $station3->setStreamUrl(streamUrl: 'http://example.com/de.mp3');
        $station3->setStationCountry(stationCountry: $country);
        $station3->addStationLanguage(stationLanguage: $languageDe);
        $this->entityManager->persist(object: $station3);

        $this->entityManager->flush();

        $crawler = $this->client->request(method: 'GET', uri: '/api/v1/station/languages');
        $this->assertSame(expected: Response::HTTP_OK, actual: $this->client->getResponse()->getStatusCode());

        $responseData = json_decode(json: $this->client->getResponse()->getContent(), associative: true);
        $this->assertCount(expectedCount: 3, haystack: $responseData);
        $this->assertSame(expected: 'de', actual: $responseData[0]['iso_639_1']);
        $this->assertSame(expected: 'fr', actual: $responseData[1]['iso_639_1']);
        $this->assertSame(expected: 'lb', actual: $responseData[2]['iso_639_1']);
    }

    /**
     * Tests that the tags endpoint returns all tags from the database.
     */
    public function testTags(): void {
        $this->createDatabase(classes: [Station::class, StationCountry::class, StationLanguage::class, StationTag::class]);

        $tagPop = new StationTag();
        $tagPop->setName(name: 'pop');
        $this->entityManager->persist(object: $tagPop);

        $tagRock = new StationTag();
        $tagRock->setName(name: 'rock');
        $this->entityManager->persist(object: $tagRock);

        $this->entityManager->flush();

        $crawler = $this->client->request(method: 'GET', uri: '/api/v1/station/tags');
        $this->assertSame(expected: Response::HTTP_OK, actual: $this->client->getResponse()->getStatusCode());

        $responseData = json_decode(json: $this->client->getResponse()->getContent(), associative: true);
        $this->assertCount(expectedCount: 2, haystack: $responseData);
        $this->assertSame(expected: 'pop', actual: $responseData[0]['name']);
        $this->assertSame(expected: 'rock', actual: $responseData[1]['name']);
    }
}
