<?php

namespace Base\Office\Repository\Booking;

use Base\Office\Entity\Booking\AppointmentType;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method AppointmentType|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppointmentType|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method AppointmentType[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentTypeRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, AppointmentType::class);
    }

    /** @return list<AppointmentType> the active types this member offers */
    public function findForMember(\Base\Office\Entity\Member $member): array
    {
        // The collection compared with one member: a join of it, as `JOIN t.members m WHERE m = :member` was.
        return $this->findBy(['members' => $member, 'active' => true], ['position' => 'ASC', 'name' => 'ASC']);
    }

}
