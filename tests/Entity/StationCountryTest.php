<?php
declare(strict_types = 1);

//
//  StationCountryTest.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Tests\Entity;

use Cappuccino\Entity\Station;
use Cappuccino\Entity\StationCountry;

use PHPUnit\Framework\TestCase;

class StationCountryTest extends TestCase {
    /**
     * Tests the setters and getters.
     */
    public function testSettersAndGetters(): void {
        $stationCountry = new StationCountry();

        $id = 99;
        $stationCountry->setId(id: $id);
        $this->assertSame(expected: $id, actual: $stationCountry->getId());

        $code = 'LU';
        $stationCountry->setISO31662(iso31662: $code);
        $this->assertSame(expected: 'LU', actual: $stationCountry->getISO31662());

        $station = new Station();
        $this->assertSame(expected: 0, actual: $stationCountry->getStations()->count());
        $stationCountry->addStation(station: $station);
        $this->assertSame(expected: 1, actual: $stationCountry->getStations()->count());
        $this->assertSame(expected: $station, actual: $stationCountry->getStations()->first());
        $stationCountry->removeStation(station: $station);
        $this->assertSame(expected: 0, actual: $stationCountry->getStations()->count());
    }
}
