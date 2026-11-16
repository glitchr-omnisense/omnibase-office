<?php

namespace Base\Office\Repository\Share;

use Base\Database\Type\Utc;
use Base\Database\Repository\Finder\Parser;
use Base\Database\Repository\Finder\Key;
use Base\Database\Repository\Finder\Group;
use Base\Database\Repository\Finder\Criterion;
use Base\Office\Entity\Share\Document;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Document[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Document::class);
    }

    /** @return list<Document> the documents kept for this user, newest first */
    public function findForRecipient(object $user, bool $withRevoked = false): array
    {
        $criteria = ['recipient' => Key::of($user)];

        return ($withRevoked ? $this->findBy($criteria, ['createdAt' => 'DESC']) : $this->findByRevokedAtNull($criteria, ['createdAt' => 'DESC'])->getResult());
    }

    /** @return list<Document> the documents this user sent */
    public function findBySender(object $user, int $limit = 50): array
    {
        return $this->findBy(['sender' => $user], ['createdAt' => 'DESC'], $limit);
    }

    public function countUnread(object $user): int
    {
        return (int) $this->countByReadAtNullAndRevokedAtNull([
            'recipient' => Key::of($user),
            Group::of(Parser::SEPARATOR_OR, Criterion::of('sender', Key::of(null)), Criterion::of('sender', Key::of($user), Parser::OPTION_NOT_EQUAL)),
        ]);
    }

    /** @return list<Document> expired or revoked before that date: their files go */
    public function findGoneBefore(\DateTimeInterface $before): array
    {
        // `x IS NOT NULL AND x < :before` is `x < :before`: no NULL is lower than a date.
        $before = Utc::from($before);

        return $this->findBy([Group::by('ExpiresAtLowerThanOrRevokedAtLowerThan', $before, $before)]);
    }

}
