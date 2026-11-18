# Changelog

## 2.0.1

- omnibridge/doctrine `^3.0|^4.0`: the bundle runs on omnibridge/doctrine 4.0 (dates read as `\DateTimeImmutable` in UTC) as on 3.x.

## 2.0.0

omnibase/office is a professional practice on its own site, whatever the profession (a health centre, a notary's or a
lawyer's office), on omnisense/omnibase: the office and its team, online booking with a member's schedule and absences,
documents kept encrypted and shared with a client, and video consultations. It names only `Base\` classes: its
repositories extend `Base\Database\Repository\ServiceEntityRepository` and speak the core's finder language, its
services write through `Base\Contract\UnitOfWork` and read other classes through
`Base\Database\Repository\Repositories`. Each query is written once in the finder language, the appointments given their
client by e-mail (`updateBy()`) and the old signals, access logs and contact requests purged (`deleteBy()`) included,
and compiled by the engine of the persistence chosen (omnibridge/doctrine or omnibridge/eloquent).
