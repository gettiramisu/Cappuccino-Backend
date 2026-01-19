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

#[Entity(repositoryClass: StationLanguageRepository::class)]
#[Table(name: 'station_languages')]
final class StationLanguage {
    public const int LENGTH_CODE = 2;

    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::SMALLINT, unique: true, nullable: false, options: ['unsigned' => true])]
    private int $id;

    #[Column(type: Types::STRING, length: self::LENGTH_CODE, unique: true, nullable: false)]
    private string $code;

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
     * @param string $code
     *
     * @return static
     */
    public function setCode(string $code): static {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string {
        return $this->code;
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
}
