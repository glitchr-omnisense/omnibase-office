<?php

namespace Base\Office\Repository;

use Base\Office\Entity\Member;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method Member|null find($id, $lockMode = null, $lockVersion = null)
 * @method Member|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Member[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Member::class);
    }

    /** @return list<Member> the team as the site shows it */
    public function findVisible(): array
    {
        return $this->findBy(['visible' => true, 'active' => true], ['position' => 'ASC', 'displayName' => 'ASC']);
    }

    /** @return list<Member> those appointments can be booked with online */
    public function findBookable(): array
    {
        return $this->findBy(['bookable' => true, 'active' => true], ['position' => 'ASC', 'displayName' => 'ASC']);
    }

    public function findOneVisibleBySlug(string $slug): ?Member
    {
        return $this->findOneBy(['slug' => $slug, 'visible' => true, 'active' => true]);
    }

    public function findOneByUser(?object $user): ?Member
    {
        return null === $user ? null : $this->findOneBy(['user' => $user]);
    }

}
