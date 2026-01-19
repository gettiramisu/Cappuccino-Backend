<?php
declare(strict_types = 1);

//
//  StationLanguageTest.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Tests\Entity;

use Cappuccino\Entity\Station;
use Cappuccino\Entity\StationLanguage;

use PHPUnit\Framework\TestCase;

class StationLanguageTest extends TestCase {
    /**
     * Tests the setters and getters.
     */
    public function testSettersAndGetters(): void {
        $stationLanguage = new StationLanguage();

        $id = 99;
        $stationLanguage->setId(id: $id);
        $this->assertSame(expected: $id, actual: $stationLanguage->getId());

        $code = 'lb';
        $stationLanguage->setISO6391(iso6391: $code);
        // The language code is expected to be lowercase.
        $this->assertSame('lb', $stationLanguage->getISO6391());

        $station = new Station();
        $this->assertSame(expected: 0, actual: $stationLanguage->getStations()->count());
        $stationLanguage->addStation(station: $station);
        $this->assertSame(expected: 1, actual: $stationLanguage->getStations()->count());
        $this->assertSame(expected: $station, actual: $stationLanguage->getStations()->first());
        $stationLanguage->removeStation(station: $station);
        $this->assertSame(expected: 0, actual: $stationLanguage->getStations()->count());
    }
}
