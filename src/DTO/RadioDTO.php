<?php
declare(strict_types = 1);

//
//  RadioDTO.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\DTO;

use Cappuccino\Entity\Station;
use Cappuccino\Entity\StationCountry;

final readonly class RadioDTO {
    public function __construct(
        public string $uuid,
        public string $name,
        public string $streamUrl,
        public ?string $homepageUrl,
        public ?string $iconUrl,
        public string $countryCode,
        public array $languageCodes,
        public array $tags
    ) {}

    /**
     * Creates a radio DTO from a radio-browser.info JSON export object.
     *
     * @param array $array The radio JSON array to create a DTO from
     *
     * @return static
     */
    public static function fromArray(array $array): static {
        $languageCodes = ($array['iso_639'] ?? '')
            |> (fn(string $x) => explode(',', $x))
            |> (fn(array $x) => array_map('strtolower', $x))
            |> (fn(array $x) => array_map('trim', $x));

        $tags = ($array['tags'] ?? '')
            |> (fn(string $x) => str_replace([';', '/', '|', ' - ', ' & '], ',', $x))
            |> (fn(string $x) => explode(',', $x))
            |> (fn(array $x) => array_map('mb_strtolower', $x))
            |> (fn(array $x) => array_map('mb_trim', $x));

        return new static(
            uuid: $array['stationuuid'],
            name: $array['name'],
            streamUrl: $array['url_stream'],
            homepageUrl: trim(string: $array['url_homepage']) ?: null,
            iconUrl: trim(string: $array['url_favicon']) ?: null,
            countryCode: strtoupper(string: $array['iso_3166_1']),
            languageCodes: $languageCodes,
            tags: $tags
        );
    }

    /**
     * Returns whether the radio DTO should be synced to the database or not.
     *
     * The API's goal is to be clean, so some requirements have to be met.
     *
     * @return bool Whether the radio DTO should be synced or not
     */
    public function shouldBeSynced(): bool {
        if (strlen(string: $this->name) > Station::LENGTH_NAME ||
            strlen(string: $this->streamUrl) > Station::LENGTH_STREAM_URL ||
            !str_starts_with(haystack: $this->streamUrl, needle: 'http') ||
            strlen(string: $this->countryCode) !== StationCountry::LENGTH_ISO_3166_2
        ) {
            return false;
        }

        return true;
    }
}
