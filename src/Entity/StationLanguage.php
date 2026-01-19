<?php
declare(strict_types = 1);

//
//  StationLanguage.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Entity;

use Cappuccino\Repository\StationLanguageRepository;

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

#[Entity(repositoryClass: StationLanguageRepository::class)]
#[Table(name: 'station_languages')]
final class StationLanguage implements JsonSerializable {
    public const int LENGTH_ISO_639_1 = 2;

    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::SMALLINT, unique: true, nullable: false, options: ['unsigned' => true])]
    private int $id;

    #[Column(name: 'iso_639_1', type: Types::STRING, length: self::LENGTH_ISO_639_1, unique: true, nullable: false)]
    private string $iso6391;

    #[ManyToMany(targetEntity: Station::class, mappedBy: 'languages')]
    private Collection $stations;

    public function __construct()
    {
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
     * @param string $iso6391
     *
     * @return static
     */
    public function setISO6391(string $iso6391): static {
        $this->iso6391 = $iso6391;
        return $this;
    }

    /**
     * @return string
     */
    public function getISO6391(): string {
        return $this->iso6391;
    }

    /**
     * @param Station $station
     *
     * @return static
     */
    public function addStation(Station $station): static {
        if (!$this->stations->contains(element: $station)) {
            $this->stations->add(element: $station);
            $station->addStationLanguage(stationLanguage: $this);
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
            $station->removeStationLanguage(stationLanguage: $this);
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
            'iso_639_1' => $this->iso6391
        ];
    }
}
