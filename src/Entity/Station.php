<?php
declare(strict_types = 1);

//
//  Station.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Entity;

use Cappuccino\Repository\StationRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

use JsonSerializable;

#[Entity(repositoryClass: StationRepository::class)]
#[Table(name: 'stations')]
final class Station implements JsonSerializable {
    public const int LENGTH_NAME = 32;
    public const int LENGTH_STREAM_URL = 256;
    public const int LENGTH_HOMEPAGE_URL = 128;
    public const int LENGTH_ICON_URL = 256;

    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER, unique: true, nullable: false, options: ['unsigned' => true])]
    private int $id;

    #[Column(type: Types::STRING, length: 36, unique: true, nullable: false)]
    private string $uuid;

    #[Column(type: Types::STRING, length: self::LENGTH_NAME, unique: false, nullable: false)]
    private string $name;

    #[Column(type: Types::STRING, length: self::LENGTH_STREAM_URL, unique: false, nullable: false)]
    private string $streamUrl;

    #[Column(type: Types::STRING, length: self::LENGTH_HOMEPAGE_URL, unique: false, nullable: true)]
    private ?string $homepageUrl = null;

    #[Column(type: Types::STRING, length: self::LENGTH_ICON_URL, unique: false, nullable: true)]
    private ?string $iconUrl = null;

    #[ManyToOne(inversedBy: 'stations')]
    #[JoinColumn(nullable: false)]
    private StationCountry $stationCountry;

    #[ManyToMany(targetEntity: StationLanguage::class, inversedBy: 'stations')]
    private Collection $stationLanguages;

    #[ManyToMany(targetEntity: StationTag::class, inversedBy: 'stations')]
    private Collection $stationTags;

    public function __construct() {
        $this->stationTags = new ArrayCollection();
        $this->stationLanguages = new ArrayCollection();
    }

    /**
     * @internal
     *
     * @param int $id
     *
     * @return static
     */
    public function setId(int $id): static {
        $this->id = $id;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getId(): ?int {
        return $this->id;
    }

    /**
     * @param string $uuid
     *
     * @return static
     */
    public function setUuid(string $uuid): static {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @return string
     */
    public function getUuid(): string {
        return $this->uuid;
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public function setName(string $name): static {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $streamUrl
     *
     * @return static
     */
    public function setStreamUrl(string $streamUrl): static {
        $this->streamUrl = $streamUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreamUrl(): string {
        return $this->streamUrl;
    }

    /**
     * @param ?string $homepageUrl
     *
     * @return static
     */
    public function setHomepageUrl(?string $homepageUrl): static {
        $this->homepageUrl = $homepageUrl;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getHomepageUrl(): ?string {
        return $this->homepageUrl;
    }

    /**
     * @param ?string $iconUrl
     *
     * @return static
     */
    public function setIconUrl(?string $iconUrl): static {
        $this->iconUrl = $iconUrl;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getIconUrl(): ?string {
        return $this->iconUrl;
    }

    /**
     * @param StationCountry $stationCountry
     *
     * @return static
     */
    public function setStationCountry(StationCountry $stationCountry): static {
        $this->stationCountry = $stationCountry;
        return $this;
    }

    /**
     * @return StationCountry
     */
    public function getStationCountry(): StationCountry {
        return $this->stationCountry;
    }

    /**
     * @return Collection<int, StationLanguage>
     */
    public function getStationLanguages(): Collection {
        return $this->stationLanguages;
    }

    /**
     * @param StationLanguage $stationLanguage
     *
     * @return static
     */
    public function addStationLanguage(StationLanguage $stationLanguage): static {
        if (!$this->stationLanguages->contains(element: $stationLanguage)) {
            $this->stationLanguages->add(element: $stationLanguage);
        }
        return $this;
    }

    /**
     * @param StationLanguage $stationLanguage
     *
     * @return static
     */
    public function removeStationLanguage(StationLanguage $stationLanguage): static {
        $this->stationLanguages->removeElement(element: $stationLanguage);
        return $this;
    }

    /**
     * @return Collection<int, StationTag>
     */
    public function getStationTags(): Collection {
        return $this->stationTags;
    }

    /**
     * @param StationTag $stationTag
     *
     * @return static
     */
    public function addStationTag(StationTag $stationTag): static {
        if (!$this->stationTags->contains(element: $stationTag)) {
            $this->stationTags->add(element: $stationTag);
        }
        return $this;
    }

    /**
     * @param StationTag $stationTag
     *
     * @return static
     */
    public function removeStationTag(StationTag $stationTag): static {
        $this->stationTags->removeElement(element: $stationTag);
        return $this;
    }

    /**
     * Returns a JSON serialized representation of the station object.
     *
     * @return array The JSON serialized representation
     */
    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'stream_url' => $this->streamUrl,
            'homepage_url' => $this->homepageUrl,
            'icon_url' => $this->iconUrl,
            'country' => $this->stationCountry,
            'languages' => $this->stationLanguages->toArray(),
            'tags' => $this->stationTags->toArray()
        ];
    }
}
