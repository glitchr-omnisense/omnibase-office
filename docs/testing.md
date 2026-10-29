---
title: "Testing"
order: 60
---

# Testing

## Running the suite

```bash
vendor/bin/phpunit
```

Inside a host application:

```bash
php vendor/bin/phpunit -c vendor/omnibase/office/phpunit.xml.dist
```

omnisense/omnibase-harness installs neither omnibase/office nor
omnisense/omnimeet. Run there without a kernel, with this checkout and
omnisense/omnimeet's (core, direct, jitsi) on the autoloader, the suite gives:

```text
OK (61 tests, 190 assertions)
```

## What the tests check

| Test | Checks |
|---|---|
| `Booking\SlotFinderTest` | twenty-minute slots; the buffer, and the last slot inside the schedule; an appointment held takes its slot and its buffer; an absence removes what it overlaps; the clients' notice and horizon, not the staff's; a schedule holds only its types and its validity; the two changes of clock |
| `Booking\AppointmentRequestsTest` | what is offered in request mode and by whom; the form opens on the member named and the only type; who takes the request; the request registered as given; nobody to take it is a booking error |
| `Controller\CrudRequiredFieldsTest` | omnibase/admin's `CrudFieldsTestCase` on the screens |
| `DependencyInjection\VisioGatewayTest` | the former keys read as written, the last file winning; with omnisense/omnimeet's bundle the direct gateway declared under the application's own registry; without it, the registry made here with the direct gateway alone; the bundle's own registry left alone |
| `DependencyInjection\VocabularyTest` | the presets; a practice's own forms; an unknown preset says which exist; the words handed to the translator; every placeholder of the texts is one of them |
| `Entity\AreaTest` | the slug from the name; the list edited one a line; paragraphs and public text; members |
| `Guard\WordingTest` | comparison and advertising found; the trade's plain words not; the site's expressions and a regime's patterns added |
| `Share\CipherTest` | a file comes back as it went; nothing of it readable once stored; an altered or truncated file refused; another master key does not open it; without a key nothing is stored; a text sealed and opened |
| `Visio\RoomTest`, `Visio\RoomsTest` | a room opens a little before and closes a while after; it keeps the gateway that opened it and its reference there; what a provider is told (a role, an opaque identifier, the member's name only); closing ends the room and purges the handshake |
