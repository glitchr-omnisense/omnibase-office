<?php

namespace Base\Office\Repository;

use Base\Database\Repository\Finder\Parser;
use Base\Database\Repository\Finder\Group;
use Base\Database\Repository\Finder\Criterion;
use Base\Office\Entity\ContactRequest;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method ContactRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactRequest|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method ContactRequest[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRequestRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, ContactRequest::class);
    }

    /** @return list<ContactRequest> */
    public function findOpen(int $limit = 5): array
    {
        return $this->findByHandledAtNull([], ['createdAt' => 'DESC'], $limit)->getResult();
    }

    public function countOpen(): int
    {
        return (int) $this->countByHandledAtNull();
    }

    public function purgeBefore(\DateTimeInterface $before): int
    {
        return $this->deleteBy([Group::of(Parser::SEPARATOR_AND, Criterion::of('createdAt', \Base\Database\Type\Utc::from($before), Parser::OPTION_LOWER))]);
    }

}
