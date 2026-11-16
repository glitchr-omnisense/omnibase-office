<?php

namespace Base\Office\Repository\Booking;

use Base\Office\Entity\Booking\Schedule;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method Schedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Schedule|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Schedule[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Schedule::class);
    }

    /** @return list<Schedule> */
    public function findForMember(\Base\Office\Entity\Member $member): array
    {
        return $this->findBy(['member' => $member], ['dayOfWeek' => 'ASC', 'startsAt' => 'ASC']);
    }

}
