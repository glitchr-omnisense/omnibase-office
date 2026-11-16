<?php

namespace Base\Office\Tests\Persistence;

use Base\Office\Entity\Booking\Appointment;
use Base\Office\Entity\ContactRequest;
use Base\Office\Entity\Share\AccessLog;
use Base\Office\Repository\Booking\AppointmentRepository;
use Base\Office\Repository\ContactRequestRepository;
use Base\Office\Repository\Share\AccessLogRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Base\Testing\Engines;
use PHPUnit\Framework\TestCase;

/** The office's purges and the appointments given to a new account: the same rows on Doctrine and on Eloquent. */
final class OfficeStatementsTest extends TestCase
{
    private static Engines $engines;

    /** @return array<string, array{0: string}> */
    public static function backends(): array
    {
        return Engines::backends();
    }

    public static function setUpBeforeClass(): void
    {
        self::$engines = Engines::of([ContactRequest::class, AccessLog::class, Appointment::class]);
    }

    /** @return list<int> */
    private static function ids(string $table): array
    {
        $pdo = self::$engines->pdo() ?? throw new \LogicException('No engines.');

        return array_map('intval', $pdo->query("SELECT id FROM $table ORDER BY id")->fetchAll(\PDO::FETCH_COLUMN));
    }

    /** @dataProvider backends */
    #[DataProvider('backends')]
    public function testThePurgesDeleteWhatIsOlder(string $backend): void
    {
        self::$engines->pdo()->exec('DELETE FROM office_contact_request; DELETE FROM office_access_log; DELETE FROM office_appointment');
        $request = static fn (string $at): int => self::$engines->insert('office_contact_request', ['name' => 'Jeanne Exemple', 'email' => 'jeanne@example.org', 'message' => 'Bonjour', 'createdAt' => $at]);
        $log = static fn (string $at): int => self::$engines->insert('office_access_log', ['documentId' => 1, 'action' => 'view', 'createdAt' => $at]);
        $appointment = static fn (?string $endsAt, string $createdAt): int => self::$engines->insert('office_appointment', ['status' => 'confirmed', 'source' => 'online', 'remindersSent' => 0, 'meta' => '[]', 'createdAt' => $createdAt, 'endsAt' => $endsAt, 'member_id' => 1, 'type_id' => 1]);
        $oldRequest = $request('2025-01-01 10:00:00');
        $newRequest = $request('2026-06-01 10:00:00');
        $oldLog = $log('2025-01-01 10:00:00');
        $newLog = $log('2026-06-01 10:00:00');
        $ended = $appointment('2025-01-01 11:00:00', '2024-12-01 10:00:00');
        $endsLater = $appointment('2026-06-01 11:00:00', '2024-12-01 10:00:00');
        $undatedOld = $appointment(null, '2025-01-01 10:00:00');
        $undatedNew = $appointment(null, '2026-06-01 10:00:00');
        $before = new \DateTimeImmutable('2026-01-01 00:00:00', new \DateTimeZone('UTC'));
        $finders = self::$engines->finders($backend);
        $requests = new ContactRequestRepository($finders);
        $logs = new AccessLogRepository($finders);
        $appointments = new AppointmentRepository($finders);

        self::assertSame(1, $requests->purgeBefore($before));
        self::assertSame(1, $logs->purgeBefore($before));
        self::assertSame(2, $appointments->purgeEndedBefore($before));
        self::assertSame([$newRequest], self::ids('office_contact_request'));
        self::assertSame([$newLog], self::ids('office_access_log'));
        self::assertSame([$endsLater, $undatedNew], self::ids('office_appointment'));
        unset($oldRequest, $oldLog, $ended, $undatedOld);
    }

    /** @dataProvider backends */
    #[DataProvider('backends')]
    public function testTheAppointmentsOfAnAddressGoToItsNewAccount(string $backend): void
    {
        $pdo = self::$engines->pdo() ?? throw new \LogicException('No engines.');
        $pdo->exec('DELETE FROM office_appointment');
        $row = static fn (?string $email, ?int $client): int => self::$engines->insert('office_appointment', ['status' => 'confirmed', 'source' => 'online', 'remindersSent' => 0, 'meta' => '[]', 'createdAt' => '2026-01-01 10:00:00', 'clientEmail' => $email, 'client_id' => $client, 'member_id' => 1, 'type_id' => 1]);
        $mine = $row('Jeanne@Example.org', null);
        $theirs = $row('jeanne@example.org', 9);
        $other = $row('autre@example.org', null);

        $attached = (new AppointmentRepository(self::$engines->finders($backend)))->attachClient(self::stub(\Base\Entity\User::class, 7), 'jeanne@example.org');

        self::assertSame(1, $attached);
        $clients = $pdo->query('SELECT id, client_id FROM office_appointment ORDER BY id')->fetchAll(\PDO::FETCH_KEY_PAIR);
        self::assertSame([$mine => 7, $theirs => 9, $other => null], array_map(static fn ($id) => null === $id ? null : (int) $id, $clients));
    }

    /** An entity of that id, never read: what a statement taking the entity is given. */
    private static function stub(string $class, int $id): object
    {
        $entity = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($class, 'id'))->setValue($entity, $id);

        return $entity;
    }
}
