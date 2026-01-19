<?php
declare(strict_types = 1);

//
//  RadioDTOTest.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Tests\DTO;

use Cappuccino\DTO\RadioDTO;
use PHPUnit\Framework\TestCase;

class RadioDTOTest extends TestCase {
    /**
     * Tests creating a radio DTO from a radio JSON.
     */
    public function testFromArray(): void {
        $radioJson = [
            'stationuuid' => '133a4c16-f338-11e9-a96c-52543be04c81',
            'name' => 'Example Radio',
            'url_stream' => 'http://example.com/stream.mp3',
            'url_homepage' => 'http://example.com/',
            'url_favicon' => 'http://example.com/icon.ico',
            'iso_3166_1' => 'lu',
            'iso_639' => 'FR',
            'tags' => 'Hits;Pop/Rock|Electronic - Dance & House'
        ];

        $dto = RadioDTO::fromArray(array: $radioJson);

        $this->assertSame(expected: '133a4c16-f338-11e9-a96c-52543be04c81', actual: $dto->uuid);
        $this->assertSame(expected: 'Example Radio', actual: $dto->name);
        $this->assertSame(expected: 'http://example.com/stream.mp3', actual: $dto->streamUrl);
        $this->assertSame(expected: 'http://example.com/', actual: $dto->homepageUrl);
        $this->assertSame(expected: 'http://example.com/icon.ico', actual: $dto->iconUrl);
        $this->assertSame(expected: 'LU', actual: $dto->countryCode);
        $this->assertSame(expected: ['fr'], actual: $dto->languageCodes);
        $this->assertSame(expected: ['hits', 'pop', 'rock', 'electronic', 'dance', 'house'], actual: $dto->tags);
    }

    /**
     * Tests if a valid radio DTO should be synced.
     */
    public function testShouldBeSyncedValid(): void {
        $validDto = new RadioDTO(
            uuid: '133a4c16-f338-11e9-a96c-52543be04c81',
            name: 'Example Radio',
            streamUrl: 'http://example.com/stream.mp3',
            homepageUrl: 'http://example.com/',
            iconUrl: 'http://example.com/icon.ico',
            countryCode: 'LU',
            languageCodes: ['fr'],
            tags: ['hits']
        );
        $this->assertTrue(condition: $validDto->shouldBeSynced());
    }

    /**
     * Tests if an invalid radio DTO should be synced with a name that's too long.
     */
    public function testShouldBeSyncedInvalidNameTooLong(): void {
        $nameTooLong = new RadioDTO(
            uuid: '133a4c16-f338-11e9-a96c-52543be04c81',
            name: str_repeat(string: 'A', times: 33),
            streamUrl: 'http://example.com/stream.mp3',
            homepageUrl: null,
            iconUrl: null,
            countryCode: 'LU',
            languageCodes: ['fr'],
            tags: ['hits']
        );
        $this->assertFalse(condition: $nameTooLong->shouldBeSynced());
    }

    /**
     * Tests if an invalid radio DTO should be synced with a stream URL that's too long.
     */
    public function testShouldBeSyncedInvalidStreamUrlTooLong(): void {
        $streamUrlTooLong = new RadioDTO(
            uuid: '133a4c16-f338-11e9-a96c-52543be04c81',
            name: 'Test Radio',
            streamUrl: 'http://example.com/' . str_repeat(string: 'a', times: 250),
            homepageUrl: null,
            iconUrl: null,
            countryCode: 'LU',
            languageCodes: ['fr'],
            tags: ['hits']
        );
        $this->assertFalse(condition: $streamUrlTooLong->shouldBeSynced());
    }

    /**
     * Tests if an invalid radio DTO should be synced with a stream URL that's not http.
     */
    public function testShouldBeSyncedInvalidStreamUrlNotHttp(): void {
        $invalidStreamUrl = new RadioDTO(
            uuid: '133a4c16-f338-11e9-a96c-52543be04c81',
            name: 'Test Radio',
            streamUrl: 'ftp://example.com/stream.mp3',
            homepageUrl: null,
            iconUrl: null,
            countryCode: 'LU',
            languageCodes: ['fr'],
            tags: ['hits']
        );
        $this->assertFalse(condition: $invalidStreamUrl->shouldBeSynced());
    }

    /**
     * Tests if an invalid radio DTO should be synced with a country code that's not ISO 3166-1 Alpha-2.
     */
    public function testShouldBeSyncedInvalidCountryCodeNotIso31661Alpha2(): void {
        $invalidCountryCode = new RadioDTO(
            uuid: '133a4c16-f338-11e9-a96c-52543be04c81',
            name: 'Test Radio',
            streamUrl: 'http://example.com/stream.mp3',
            homepageUrl: null,
            iconUrl: null,
            countryCode: 'LUX',
            languageCodes: ['fr'],
            tags: ['hits']
        );
        $this->assertFalse(condition: $invalidCountryCode->shouldBeSynced());
    }
}
