<?php
declare(strict_types = 1);

//
//  StationTag.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Entity;

use Cappuccino\Repository\StationTagRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;

use JsonSerializable;

#[Entity(repositoryClass: StationTagRepository::class)]
#[Table(name: 'station_tags')]
final class StationTag implements JsonSerializable {
    public const array ALLOWED_TAGS = [
        '50s',
        '60s',
        '70s',
        '80s',
        '90s',
        '2000s',
        '2010s',
        '2020s',
        'alternative',
        'ambient',
        'blues',
        'chill',
        'classic',
        'country',
        'culture',
        'dance',
        'disco',
        'downtempo',
        'dubstep',
        'electronic',
        'entertainment',
        'folk',
        'funk',
        'groove',
        'hard rock',
        'hits',
        'house',
        'indie',
        'jazz',
        'kpop',
        'latin',
        'lounge',
        'metal',
        'news',
        'phonk',
        'pop',
        'progressive',
        'rap',
        'reggae',
        'religious',
        'rock',
        'schlager',
        'sleep',
        'soul',
        'sports',
        'storytelling',
        'techno',
        'trance'
    ];

    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::SMALLINT, unique: true, nullable: false, options: ['unsigned' => true])]
    private int $id;

    #[Column(type: Types::STRING, length: 16, unique: true, nullable: false)]
    private string $name;

    #[ManyToMany(targetEntity: Station::class, mappedBy: 'stationTags')]
    private Collection $stations;

    public function __construct() {
        $this->stations = new ArrayCollection();
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
     * @param Station $station
     *
     * @return static
     */
    public function addStation(Station $station): static {
        if (!$this->stations->contains(element: $station)) {
            $this->stations->add(element: $station);
            $station->addStationTag(stationTag: $this);
        }
        return $this;
    }

    /**
     * @param Station $station
     *
     * @return static
     */
    public function removeStation(Station $station): static {
        if ($this->stations->removeElement(element: $station)) {
            $station->removeStationTag(stationTag: $this);
        }
        return $this;
    }

    /**
     * @return Collection<int, Station>
     */
    public function getStations(): Collection {
        return $this->stations;
    }

    /**
     * Returns a JSON serialized representation of the station object.
     *
     * @return array The JSON serialized representation
     */
    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}
