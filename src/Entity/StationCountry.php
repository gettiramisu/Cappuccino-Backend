<?php
declare(strict_types = 1);

//
//  StationCountry.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Entity;

use Cappuccino\Repository\StationCountryRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

use JsonSerializable;

#[Entity(repositoryClass: StationCountryRepository::class)]
#[Table(name: 'station_countries')]
final class StationCountry implements JsonSerializable {
    public const int LENGTH_ISO_3166_2 = 2;

    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::SMALLINT, unique: true, nullable: false, options: ['unsigned' => true])]
    private int $id;

    #[Column(name: 'iso_3166_2', type: Types::STRING, length: self::LENGTH_ISO_3166_2, unique: true, nullable: false)]
    private string $iso31662;

    #[OneToMany(targetEntity: Station::class, mappedBy: 'stationCountry')]
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
     * @param string $iso31662
     *
     * @return static
     */
    public function setISO31662(string $iso31662): static {
        $this->iso31662 = $iso31662;
        return $this;
    }

    /**
     * @return string
     */
    public function getISO31662(): string {
        return $this->iso31662;
    }

    /**
     * @param Station $station
     *
     * @return static
     */
    public function addStation(Station $station): static {
        if (!$this->stations->contains(element: $station)) {
            $this->stations->add(element: $station);
            $station->setStationCountry(stationCountry: $this);
        }
        return $this;
    }

    /**
     * @param Station $station
     *
     * @return static
     */
    public function removeStation(Station $station): static {
        $this->stations->removeElement(element: $station);
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
            'iso_3166_2' => $this->iso31662
        ];
    }
}
