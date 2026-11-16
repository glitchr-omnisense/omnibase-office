<?php

namespace Base\Office\Repository\Visio;

use Base\Contract\UnitOfWork;
use Base\Database\Repository\Finder\Parser;
use Base\Database\Repository\Finder\Key;
use Base\Database\Repository\Finder\Group;
use Base\Database\Repository\Finder\Criterion;
use App\Entity\User;
use Base\Office\Entity\Visio\Room;
use Base\Office\Entity\Visio\Signal;
use Base\Database\Repository\Finders;
use Omnimeet\Direct\Signal as Message;
use Omnimeet\Direct\SignalStoreInterface;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * The handshake's storage: omnimeet/direct's rules (Omnimeet\Direct\Signaling)
 * keep their messages here, as Signal rows - a room named by its reference
 * on the gateway, a participant by "u<id>" (Base\Office\Visio\Rooms::participantId()).
 *
 * @method Signal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Signal[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class SignalRepository extends ServiceEntityRepository implements SignalStoreInterface
{
    /** @var array<string, int> the rooms' ids by reference: they never change */
    private array $rooms = [];

    public function __construct(
        Finders $finders,
        private readonly UnitOfWork $unitOfWork,
        private readonly RoomRepository $roomRepository,
    ) {
        parent::__construct($finders, Signal::class);
    }

    public function append(string $room, string $sender, string $type, string $payload): int
    {
        $signal = new Signal(
            $this->unitOfWork->reference(Room::class, $this->roomId($room) ?? throw new \InvalidArgumentException(\sprintf('No room "%s".', $room))),
            $this->unitOfWork->reference(User::class, self::userId($sender) ?? throw new \InvalidArgumentException(\sprintf('"%s" is not a participant.', $sender))),
            $type,
            $payload,
        );
        $this->unitOfWork->persist($signal);
        $this->unitOfWork->flush();

        return (int) $signal->getId();
    }

    public function read(string $room, int $after = 0, ?string $except = null, int $limit = 200): array
    {
        $id = $this->roomId($room);
        if (null === $id) {
            return [];
        }
        // The rows were read as arrays (id, IDENTITY(sender), type, payload); they are read as signals now, the
        // sender's id taken from its reference without loading it..
        $criteria = ['room' => Key::of($id)];
        if (null !== $except) {
            $criteria[] = Group::of(Parser::SEPARATOR_AND, Criterion::of('sender', Key::of(self::userId($except) ?? 0), Parser::OPTION_NOT_EQUAL));
        }

        return array_map(
            static fn (Signal $signal) => new Message((int) $signal->getId(), $room, 'u'.$signal->getSender()?->getId(), (string) $signal->getType(), (string) $signal->getPayload()),
            $this->findByIdGreaterThan($after, $criteria, ['id' => 'ASC'], $limit)->getResult(),
        );
    }

    public function purge(string $room): int
    {
        $id = $this->roomId($room);

        return null === $id ? 0 : $this->deleteBy(['room' => $id]);
    }

    /** @return list<Signal> what the other side sent after that id */
    public function findForRecipient(Room $room, object $user, int $after = 0): array
    {
        return $this->findByIdGreaterThan($after, [
            'room' => Key::of($room),
            Group::of(Parser::SEPARATOR_AND, Criterion::of('sender', Key::of($user), Parser::OPTION_NOT_EQUAL)),
        ], ['id' => 'ASC'], 200)->getResult();
    }

    public function purgeRoom(Room $room): int
    {
        return $this->deleteBy(['room' => $room]);
    }

    public function countForRoom(Room $room): int
    {
        return $this->count(['room' => Key::of($room)]);
    }

    private function roomId(string $reference): ?int
    {
        if (!isset($this->rooms[$reference])) {
            // The room's id was selected alone; the room is read now (once per reference, kept below).
            $id = $this->roomRepository->findOneBy(['reference' => $reference])?->getId();
            if (null === $id) {
                return null;
            }
            $this->rooms[$reference] = (int) $id;
        }

        return $this->rooms[$reference];
    }

    private static function userId(string $participant): ?int
    {
        return preg_match('/^u(\d+)$/', $participant, $m) ? (int) $m[1] : null;
    }
}
