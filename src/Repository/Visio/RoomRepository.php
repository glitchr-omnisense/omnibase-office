<?php

namespace Base\Office\Repository\Visio;

use Base\Database\Type\Utc;
use Base\Office\Entity\Visio\Room;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method Room|null find($id, $lockMode = null, $lockVersion = null)
 * @method Room|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Room[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Room::class);
    }

    public function findOneByToken(string $token): ?Room
    {
        return $this->findOneBy(['token' => $token]);
    }

    public function findOneBySubject(string $key): ?Room
    {
        return $this->findOneBy(['subjectKey' => $key]);
    }

    /** The room a gateway knows by that name (omnisense/omnimeet's reference), on that gateway when it is given. */
    public function findOneByReference(string $reference, ?string $gateway = null): ?Room
    {
        return $this->findOneBy(['reference' => $reference] + (null !== $gateway ? ['gateway' => $gateway] : []));
    }

    /** @return list<Room> rooms open now whose guest waits for the host */
    public function findWaiting(int $seconds = 20): array
    {
        $now = Utc::now();

        return array_values(array_filter(
            $this->findByEndedAtNullAndOpensAtLowerEqualToAndClosesAtGreaterEqualToAndGuestSeenAtGreaterEqualTo(
                $now, $now, $now->modify(sprintf('-%d seconds', $seconds)),
            )->getResult(),
            static fn (Room $room) => $room->isGuestWaiting($seconds, $now),
        ));
    }

    /** @return list<Room> closed rooms whose handshake can go */
    public function findClosedBefore(\DateTimeInterface $before): array
    {
        return $this->findByClosesAtLowerThanOrEndedAtNotNull(Utc::from($before))->getResult();
    }

}
