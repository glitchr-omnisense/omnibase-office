<?php

namespace Base\Office\Repository\Booking;

use Base\Database\Repository\Finder\Parser;
use Base\Database\Repository\Finder\Criterion;
use Base\Office\Entity\Booking\Appointment;
use Base\Office\Enum\AppointmentStatus;
use Base\Database\Repository\Finder\Group;
use Base\Database\Repository\Finder\Key;
use Base\Database\Type\Utc;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * The appointments. The moments compared are given in UTC (Utc::from()), the zone the columns keep, and bound
 * with the column's type by the finder; the member and the client are compared by their key (Key::of()), the
 * row's own foreign key as `a.member = :member` read it, without a join.
 *
 * @method Appointment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appointment|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Appointment[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Appointment::class);
    }

    /** @return list<Appointment> the member's appointments holding a slot in [from, to) */
    public function findActiveBetween(\Base\Office\Entity\Member $member, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->findByStartsAtNotNullAndStartsAtLowerThanAndEndsAtGreaterThan(
            Utc::from($to), Utc::from($from),
            ['member' => Key::of($member), 'status' => self::active()],
            ['startsAt' => 'ASC'],
        )->getResult();
    }

    /** @return list<Appointment> every appointment of the day(s), all members, for the agenda */
    public function findBetween(\DateTimeInterface $from, \DateTimeInterface $to, ?\Base\Office\Entity\Member $member = null, bool $withCancelled = false): array
    {
        // The member and the type were fetch-joined (one query for the agenda); they load lazily now, the rows the
        // same (both associations are required: the inner joins kept every appointment).
        $criteria = $member ? ['member' => Key::of($member)] : [];
        if (!$withCancelled) {
            $criteria[] = Group::by('StatusNotEqualTo', AppointmentStatus::CANCELLED->value);
        }

        return $this->findByStartsAtGreaterEqualToAndStartsAtLowerThan(Utc::from($from), Utc::from($to), $criteria, ['startsAt' => 'ASC'])->getResult();
    }

    /** @return list<Appointment> the client's appointments, upcoming first then the past */
    public function findForClient(object $client, bool $upcoming = true, int $limit = 50): array
    {
        $now = Utc::now();
        $criteria = ['client' => Key::of($client)];
        if ($upcoming) {
            return $this->findBy($criteria + [Group::by('StartsAtGreaterEqualToOrStartsAtNull', $now), 'status' => self::active()], ['startsAt' => 'ASC'], $limit);
        }

        return $this->findBy($criteria + [Group::by('StartsAtLowerThanOrStatusNotEqualTo', $now, self::active())], ['startsAt' => 'DESC'], $limit);
    }

    /** Whether the client had an appointment with that member before (BookableBy::KNOWN). */
    public function hasSeen(object $client, \Base\Office\Entity\Member $member): bool
    {
        return $this->count([
            'client' => Key::of($client),
            'member' => Key::of($member),
            'status' => [AppointmentStatus::CONFIRMED->value, AppointmentStatus::DONE->value],
        ]) > 0;
    }

    public function findOneByCancelToken(string $token): ?Appointment
    {
        return $this->findOneBy(['cancelTokenHash' => hash('sha256', $token)]);
    }

    /** @return list<Appointment> confirmed ones starting within [from, to), for the reminders */
    public function findConfirmedStartingBetween(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->findByStartsAtGreaterEqualToAndStartsAtLowerThan(Utc::from($from), Utc::from($to), ['status' => AppointmentStatus::CONFIRMED->value])->getResult();
    }

    /** @return list<Appointment> past ones still CONFIRMED: to close */
    public function findToClose(\DateTimeInterface $before): array
    {
        return $this->findByEndsAtLowerThan(Utc::from($before), ['status' => AppointmentStatus::CONFIRMED->value])->getResult();
    }

    /** @return list<Appointment> requests waiting for the office */
    public function findRequested(int $limit = 20): array
    {
        return $this->findBy(['status' => AppointmentStatus::REQUESTED->value], ['createdAt' => 'ASC'], $limit);
    }

    /** @return list<Appointment> */
    public function findSeries(string $series): array
    {
        return $this->findBy(['series' => $series], ['startsAt' => 'ASC']);
    }

    public function purgeEndedBefore(\DateTimeInterface $before): int
    {
        // Ended before, or never dated and made before.
        $before = Utc::from($before);

        return $this->deleteBy([Group::of(Parser::SEPARATOR_OR,
            Criterion::of('endsAt', $before, Parser::OPTION_LOWER),
            Group::of(Parser::SEPARATOR_AND, Criterion::of('endsAt', null, Parser::OPTION_NULL), Criterion::of('createdAt', $before, Parser::OPTION_LOWER)),
        )]);
    }

    /**
     * The appointments taken for that address without an account become the client's (moved here from
     * Base\Office\Service\ClientInvitations::attach()). A statement: a bulk UPDATE.
     */
    public function attachClient(object $client, string $email): int
    {
        return $this->updateBy([
            'client' => Key::of(null),
            Group::of(Parser::SEPARATOR_AND, Criterion::of('clientEmail', mb_strtolower($email), Parser::OPTION_INSENSITIVE)),
        ], ['client' => $client]);
    }

    /** @return list<string> the statuses of an appointment that holds its slot */
    private static function active(): array
    {
        return [AppointmentStatus::REQUESTED->value, AppointmentStatus::CONFIRMED->value];
    }

}
