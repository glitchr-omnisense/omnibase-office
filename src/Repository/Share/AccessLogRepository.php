<?php

namespace Base\Office\Repository\Share;

use Base\Database\Repository\Finder\Parser;
use Base\Database\Repository\Finder\Group;
use Base\Database\Repository\Finder\Criterion;
use Base\Database\Repository\Finder\Key;
use Base\Office\Entity\Share\AccessLog;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method AccessLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccessLog|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method AccessLog[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class AccessLogRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, AccessLog::class);
    }

    /** @return list<AccessLog> newest first */
    public function findLatest(int $limit = 100, ?int $documentId = null, ?object $user = null): array
    {
        $criteria = (null !== $documentId ? ['documentId' => $documentId] : []) + (null !== $user ? ['user' => Key::of($user)] : []);

        return $this->findBy($criteria, ['createdAt' => 'DESC', 'id' => 'DESC'], $limit);
    }

    /** @return list<AccessLog> what was done with this person's documents */
    public function findForRecipient(int $recipientId, int $limit = 200): array
    {
        return $this->findBy(['recipientId' => $recipientId], ['createdAt' => 'DESC'], $limit);
    }

    public function purgeBefore(\DateTimeInterface $before): int
    {
        return $this->deleteBy([Group::of(Parser::SEPARATOR_AND, Criterion::of('createdAt', \Base\Database\Type\Utc::from($before), Parser::OPTION_LOWER))]);
    }

}
