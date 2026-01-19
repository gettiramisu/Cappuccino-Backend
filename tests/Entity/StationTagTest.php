<?php
declare(strict_types = 1);

//
//  StationTagTest.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Tests\Entity;

use Cappuccino\Entity\Station;
use Cappuccino\Entity\StationTag;

use PHPUnit\Framework\TestCase;

class StationTagTest extends TestCase {
    /**
     * Tests the setters and getters.
     */
    public function testSettersAndGetters(): void {
        $stationTag = new StationTag();

        $id = 99;
        $stationTag->setId(id: $id);
        $this->assertSame(expected: $id, actual: $stationTag->getId());

        $code = 'pop';
        $stationTag->setName(name: $code);
        // The tag name is expected to be lowercase.
        $this->assertSame(expected: 'pop', actual: $stationTag->getName());

        $station = new Station();
        $this->assertSame(expected: 0, actual: $stationTag->getStations()->count());
        $stationTag->addStation(station: $station);
        $this->assertSame(expected: 1, actual: $stationTag->getStations()->count());
        $this->assertSame(expected: $station, actual: $stationTag->getStations()->first());
        $stationTag->removeStation(station: $station);
        $this->assertSame(expected: 0, actual: $stationTag->getStations()->count());
    }
}
