<?php

namespace Base\Office\Repository\Booking;

use Base\Office\Entity\Booking\ServiceArea;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method ServiceArea|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceArea|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method ServiceArea[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceAreaRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, ServiceArea::class);
    }

    /** @return list<ServiceArea> */
    public function findActive(): array
    {
        return $this->findBy(['active' => true], ['name' => 'ASC']);
    }

    /** The first active area holding that address, or null: outside every area. */
    public function findContaining(?string $postalCode, ?string $town = null, ?float $latitude = null, ?float $longitude = null): ?ServiceArea
    {
        foreach ($this->findActive() as $area) {
            if ($area->contains($postalCode, $town, $latitude, $longitude)) {
                return $area;
            }
        }

        return null;
    }

}
