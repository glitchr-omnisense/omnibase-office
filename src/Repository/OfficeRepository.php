<?php

namespace Base\Office\Repository;

use Base\Office\Entity\Office;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method Office|null find($id, $lockMode = null, $lockVersion = null)
 * @method Office|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Office[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class OfficeRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Office::class);
    }

    public function findMain(): ?Office
    {
        return $this->findOneBy(['main' => true], ['position' => 'ASC']) ?? $this->findOneBy([], ['position' => 'ASC']);
    }

    /** @return list<Office> */
    public function findOrdered(): array
    {
        return $this->findBy([], ['main' => 'DESC', 'position' => 'ASC', 'name' => 'ASC']);
    }

}
