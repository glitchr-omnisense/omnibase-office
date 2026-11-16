<?php

namespace Base\Office\Repository;

use Base\Office\Entity\WalkIn;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method WalkIn|null find($id, $lockMode = null, $lockVersion = null)
 * @method WalkIn|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method WalkIn[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class WalkInRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, WalkIn::class);
    }

    /** @return list<WalkIn> */
    public function findActive(): array
    {
        return $this->findBy(['active' => true], ['opensAt' => 'ASC', 'id' => 'ASC']);
    }

}
