# omnibase/office

A professional practice on its own site, whatever the profession - a health centre, a notary's or a
lawyer's office - on [omnisense/omnibase](https://github.com/glitchr-omnisense/omnibase). One bundle, four parts:

| Part | What it is |
|---|---|
| **Core** | The office (`Office`: address, access, accessibility), the team (`Member`, read again from their professional register), the walk-in hours, the contact form, the compliance checks. The opening hours are omnibase's. |
| **Booking** | Appointment types, weekly schedules, absences, service areas; free slots (`SlotFinder`), bookings taken under a lock (`Booker`), requests without a time, series; e-mails with the `.ics`; the agenda in the back office. |
| **Share** | A document vault per recipient: files encrypted with libsodium (a key per file, wrapped by a master key), fail closed without a key; who may read decided by a voter a regime extends; every access logged. |
| **Visio** | A video room per appointment, with a waiting room. The call is carried by a gateway of [omnisense/omnimeet](https://github.com/glitchr-omnisense/omnimeet): by default from browser to browser (`omnimeet/direct`: WebRTC, the handshake relayed over HTTP, temporary TURN credentials for your own coturn, nothing through a third party); a Jitsi instance or another provider by configuration. Who enters, who waits and when it ends stay the room's. |

Nothing here knows health, law or payment. A regime puts its trade on top: `omnibase/health` does.

```sh
composer require omnibase/office
```

Stable releases follow semantic versioning: this installs the latest 1.x, with omnisense/omnibase 5.1 or later
on Symfony (omnibridge/symfony) and Doctrine ORM (DoctrineBundle). No `minimum-stability` setting is needed.

Under Symfony, register the bundle and its pages, then create its tables:

```php
// config/bundles.php
Base\Office\OfficeBundle::class => ['all' => true],
Omnimeet\Bridge\Symfony\OmnimeetBundle::class => ['all' => true],   // the video gateways; optional for the direct one alone
```

```yaml
# config/routes.yaml
office_controller:
    resource: "@OfficeBundle/src/Controller/Client"
    type: attribute
```

```sh
bin/console make:migration && bin/console doctrine:migrations:migrate
```

First use: describe the office in the back office (or as an `Office` entity in your fixtures), then open
`/rendez-vous/demande`, the appointment request form anyone can send. The vault's master key, the booking and the
visio are set up in [docs/](docs/index.md).

Documentation: [docs/](docs/index.md). License: MIT since 2026-10-09; earlier versions remain published under LGPL-3.0-or-later.
