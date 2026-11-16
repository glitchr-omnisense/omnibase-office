<?php

namespace Base\Office\Repository\Booking;

use Base\Database\Type\Utc;
use Base\Database\Repository\Finder\Key;
use Base\Office\Entity\Booking\Absence;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method Absence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Absence|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Absence[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class AbsenceRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Absence::class);
    }

    /** @return list<Absence> the member's absences overlapping [from, to) */
    public function findOverlapping(\Base\Office\Entity\Member $member, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->findByStartsAtLowerThanAndEndsAtGreaterThan(Utc::from($to), Utc::from($from), ['member' => Key::of($member)])->getResult();
    }

}
