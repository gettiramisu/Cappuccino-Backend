<?php
declare(strict_types = 1);

//
//  SyncRadiosCommand.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Command;

use Cappuccino\DTO\RadioDTO;
use Cappuccino\Entity\Station;
use Cappuccino\Entity\StationCountry;
use Cappuccino\Entity\StationLanguage;
use Cappuccino\Entity\StationTag;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SyncRadiosCommand extends AbstractCommand {
    private array $radioJson;
    private array $stationCountries;
    private array $stationLanguages;
    private array $stationTags;

    public function __construct() {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void {
        $this->setName(name: 'cappuccino:sync')
             ->setDescription(description: 'Syncs the latest radio-browser.info JSON export with the database.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->radioJson = $this->fetchLatestRadioJsonExport();

        $this->entityManager->beginTransaction();

        try {
            // Clear all tables. Order matters due to foreign key constraints:
            // Stations first (references countries/languages/tags), then lookup tables.
            $this->entityManager->createQuery(dql: 'DELETE FROM ' . Station::class)->execute();
            $this->entityManager->createQuery(dql: 'DELETE FROM ' . StationCountry::class)->execute();
            $this->entityManager->createQuery(dql: 'DELETE FROM ' . StationLanguage::class)->execute();
            $this->entityManager->createQuery(dql: 'DELETE FROM ' . StationTag::class)->execute();

            // Insert fresh data from export.
            $this->stationCountries = [];
            $this->stationLanguages = [];
            $this->stationTags = [];

            $this->insertCountries();
            $this->insertLanguages();
            $this->insertTags();
            $this->insertRadios();

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return Command::SUCCESS;
    }

    /**
     * Inserts countries from the radio-browser.info JSON export.
     */
    private function insertCountries(): void {
        foreach ($this->radioJson as $json) {
            $radio = RadioDTO::fromArray(array: $json);
            if (!$radio->shouldBeSynced()) {
                continue;
            }

            if (!array_key_exists(key: $radio->countryCode, array: $this->stationCountries)) {
                $stationCountry = new StationCountry();
                $stationCountry->setISO31662(iso31662: $radio->countryCode);
                $this->entityManager->persist(object: $stationCountry);

                $this->stationCountries[$radio->countryCode] = $stationCountry;
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Inserts languages from the radio-browser.info JSON export.
     */
    private function insertLanguages(): void {
        foreach ($this->radioJson as $json) {
            $radio = RadioDTO::fromArray(array: $json);
            if (!$radio->shouldBeSynced()) {
                continue;
            }

            foreach ($radio->languageCodes as $languageCode) {
                if (strlen(string: $languageCode) !== StationLanguage::LENGTH_ISO_639_1) {
                    continue;
                }

                if (!array_key_exists(key: $languageCode, array: $this->stationLanguages)) {
                    $stationLanguage = new StationLanguage();
                    $stationLanguage->setISO6391(iso6391: $languageCode);
                    $this->entityManager->persist(object: $stationLanguage);

                    $this->stationLanguages[$languageCode] = $stationLanguage;
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Inserts tags from the radio-browser.info JSON export.
     */
    private function insertTags(): void {
        foreach ($this->radioJson as $json) {
            $radio = RadioDTO::fromArray(array: $json);
            if (!$radio->shouldBeSynced()) {
                continue;
            }

            $mappedTags = $this->tagMapper->mapMany(rawTags: $radio->tags);

            foreach ($mappedTags as $tag) {
                if (!array_key_exists(key: $tag, array: $this->stationTags)) {
                    $stationTag = new StationTag();
                    $stationTag->setName(name: $tag);
                    $this->entityManager->persist(object: $stationTag);

                    $this->stationTags[$tag] = $stationTag;
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Inserts stations from the radio-browser.info JSON export.
     */
    private function insertRadios(): void {
        foreach ($this->radioJson as $json) {
            $radio = RadioDTO::fromArray(array: $json);
            if (!$radio->shouldBeSynced()) {
                continue;
            }

            $station = new Station();
            $station->setUuid(uuid: $radio->uuid);
            $station->setName(name: $radio->name);
            $station->setStreamUrl(streamUrl: $radio->streamUrl);
            $station->setHomepageUrl(homepageUrl: $this->isValidHomepageUrl(url: $radio->homepageUrl) ? $radio->homepageUrl : null);
            $station->setIconUrl(iconUrl: $this->isValidIconUrl(url: $radio->iconUrl) ? $radio->iconUrl : null);
            $station->setStationCountry(stationCountry: $this->stationCountries[$radio->countryCode]);

            foreach ($radio->languageCodes as $languageCode) {
                if (array_key_exists(key: $languageCode, array: $this->stationLanguages)) {
                    $station->addStationLanguage(stationLanguage: $this->stationLanguages[$languageCode]);
                }
            }

            $mappedTags = $this->tagMapper->mapMany(rawTags: $radio->tags);
            foreach ($mappedTags as $tag) {
                if (array_key_exists(key: $tag, array: $this->stationTags)) {
                    $station->addStationTag(stationTag: $this->stationTags[$tag]);
                }
            }

            $this->entityManager->persist(object: $station);
        }

        $this->entityManager->flush();
    }

    /**
     * Returns whether a homepage URL is valid for the database.
     *
     * Some exported radios have overly long or straight up not URLs.
     *
     * @param ?string $url The URL to check
     *
     * @return bool Whether the homepage URL is valid or not
     */
    private function isValidHomepageUrl(?string $url): bool {
        return $url && str_starts_with(haystack: $url, needle: 'http') && strlen(string: $url) <= Station::LENGTH_HOMEPAGE_URL;
    }

    /**
     * Returns whether an icon URL is valid for the database.
     *
     * Some exported radios have overly long or straight up not URLs.
     *
     * @param ?string $url The URL to check
     *
     * @return bool Whether the icon URL is valid or not
     */
    private function isValidIconUrl(?string $url): bool {
        return $url && str_starts_with(haystack: $url, needle: 'http') && strlen(string: $url) <= Station::LENGTH_ICON_URL;
    }
}
