<?php
declare(strict_types = 1);

//
//  StationTest.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Tests\Entity;

use Cappuccino\Entity\Station;
use Cappuccino\Entity\StationCountry;
use Cappuccino\Entity\StationLanguage;
use Cappuccino\Entity\StationTag;

use PHPUnit\Framework\TestCase;

final class StationTest extends TestCase {
    /**
     * Tests the setters and getters.
     */
    public function testSettersAndGetters(): void {
        $station = new Station();

        $id = 99;
        $station->setId(id: $id);
        $this->assertSame(expected: $id, actual: $station->getId());

        $uuid = '9611e44f-0601-11e8-ae97-52543be04c81';
        $station->setUuid(uuid: $uuid);
        $this->assertSame(expected: $uuid, actual: $station->getUuid());

        $name = 'Eldo';
        $station->setName(name: $name);
        $this->assertSame(expected: $name, actual: $station->getName());

        $streamUrl = 'http://example.com/stream.mp3';
        $station->setStreamUrl(streamUrl: $streamUrl);
        $this->assertSame(expected: $streamUrl, actual: $station->getStreamUrl());

        $homepageUrl = 'https://example.com';
        $station->setHomepageUrl(homepageUrl: $homepageUrl);
        $this->assertSame(expected: $homepageUrl, actual: $station->getHomepageUrl());

        $iconUrl = 'https://example.com/icon.png';
        $station->setIconUrl(iconUrl: $iconUrl);
        $this->assertSame(expected: $iconUrl, actual: $station->getIconUrl());

        $stationCountry = new StationCountry();
        $station->setStationCountry(stationCountry: $stationCountry);
        $this->assertSame(expected: $stationCountry, actual: $station->getStationCountry());

        $stationLanguage = new StationLanguage();
        $this->assertSame(expected: 0, actual: $station->getStationLanguages()->count());
        $station->addStationLanguage(stationLanguage: $stationLanguage);
        $this->assertSame(expected: 1, actual: $station->getStationLanguages()->count());
        $this->assertSame(expected: $stationLanguage, actual: $station->getStationLanguages()->first());
        $station->removeStationLanguage(stationLanguage: $stationLanguage);
        $this->assertSame(expected: 0, actual: $station->getStationLanguages()->count());

        $stationTag = new StationTag();
        $this->assertSame(expected: 0, actual: $station->getStationTags()->count());
        $station->addStationTag(stationTag: $stationTag);
        $this->assertSame(expected: 1, actual: $station->getStationTags()->count());
        $this->assertSame(expected: $stationTag, actual: $station->getStationTags()->first());
        $station->removeStationTag(stationTag: $stationTag);
        $this->assertSame(expected: 0, actual: $station->getStationTags()->count());
    }

    /**
     * Tests the JSON serialized representation of a station object.
     */
    public function testJsonSerialize(): void {
        $station = new Station();
        $station->setUuid(uuid: '960ef824-0601-11e8-ae97-52543be04c81');
        $station->setName(name: 'Example Radio');
        $station->setStreamUrl(streamUrl: 'http://example.com/stream.mp3');
        $station->setHomepageUrl(homepageUrl: 'http://example.com');
        $station->setIconUrl(iconUrl: 'http://example.com/icon.png');
        $station->setStationCountry(stationCountry: (new StationCountry)->setCode(code: 'LU'));
        $station->addStationLanguage(stationLanguage: (new StationLanguage)->setCode(code: 'lb'));
        $station->addStationTag(stationTag: (new StationTag)->setName(name: 'pop'));

        $this->assertSame(expected: [
            'uuid' => '960ef824-0601-11e8-ae97-52543be04c81',
            'name' => 'Example Radio',
            'stream_url' => 'http://example.com/stream.mp3',
            'homepage_url' => 'http://example.com',
            'icon_url' => 'http://example.com/icon.png',
            'country' => 'LU',
            'languages' => ['lb'],
            'tags' => ['pop']
        ], actual: $station->jsonSerialize());
    }
}
