<?php

namespace Base\Office\Repository;

use Base\Office\Entity\Area;
use Base\Office\Entity\Member;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * The fields of practice of a regime (Base\Office\Entity\Area): its
 * repository extends this one and names its entity.
 *
 *     class AreaRepository extends \Base\Office\Repository\AreaRepository
 *     {
 *         public function __construct(Finders $finders) { parent::__construct($finders, Area::class); }
 *     }
 *
 * @template T of Area
 */
abstract class AreaRepository extends ServiceEntityRepository
{
    /** @return list<T> */
    public function findActive(): array
    {
        return $this->findBy(['active' => true], ['position' => 'ASC', 'name' => 'ASC']);
    }

    /** @return T|null */
    public function findOneActiveBySlug(string $slug): ?Area
    {
        return $this->findOneBy(['slug' => $slug, 'active' => true]);
    }

    /** @return list<T> the fields a member follows */
    public function findForMember(Member $member): array
    {
        // The collection compared with one member: a join of it, as `JOIN a.members m WHERE m = :member` was.
        return $this->findBy(['members' => $member, 'active' => true], ['position' => 'ASC']);
    }
}
