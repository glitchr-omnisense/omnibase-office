---
title: "Reference"
order: 45
---

# Reference

## Routes

| Name | Path | Methods | Access |
|---|---|---|---|
| `office_team` | `/equipe` | GET | everyone |
| `office_member` | `/equipe/{slug}` | GET | everyone |
| `office_hours` | `/horaires-acces` | GET | everyone |
| `office_contact` | `/contact` | GET, POST | everyone |
| `office_request` | `/rendez-vous/demande` | GET, POST | everyone, when `booking.public_request` is on |
| `office_booking` | `/rendez-vous` | GET | everyone |
| `office_booking_member` | `/rendez-vous/{slug}` | GET | everyone |
| `office_booking_slots` | `/rendez-vous/{slug}/{type}` | GET, POST | everyone |
| `office_booking_confirm` | `/rendez-vous/{slug}/{type}/{start}` | GET, POST | `ROLE_USER` |
| `office_booking_done` | `/rendez-vous/confirmation/{id}` | GET | `ROLE_USER` |
| `office_booking_ics` | `/rendez-vous/{id}.ics` | GET | `ROLE_USER` |
| `office_booking_cancel` | `/rendez-vous/annuler/{token}` | GET, POST | the holder of the cancel token (48 hex characters) |
| `office_space_appointments` | `/espace/rendez-vous` | GET | `ROLE_USER` |
| `office_space_cancel` | `/espace/rendez-vous/{id}/annuler` | POST | `ROLE_USER` |
| `office_space_documents` | `/espace/documents` | GET | `ROLE_USER` |
| `office_document_download` | `/espace/documents/{id}/telecharger` | GET | `ROLE_USER`, the vault's audience |
| `office_document_deposit` | `/espace/documents/deposer` | POST | `ROLE_USER` |
| `office_document_revoke` | `/espace/documents/{id}/retirer` | POST | `ROLE_USER` |
| `office_invitation` | `/invitation/{token}` | GET, POST | the invited client |
| `office_visio_room` | `/visio/{token}` | GET | `ROLE_USER` |
| `office_visio_engine` | `/visio/{token}/engine.js` | GET | `ROLE_USER` |
| `office_visio_ice` | `/visio/{token}/ice` | GET | `ROLE_USER` |
| `office_visio_signal_send` | `/visio/{token}/signal` | POST | `ROLE_USER` |
| `office_visio_signal_poll` | `/visio/{token}/signal` | GET | `ROLE_USER` |
| `office_visio_end` | `/visio/{token}/fin` | POST | `ROLE_USER` |
| `office_visio_document` | `/visio/{token}/document` | POST | `ROLE_USER` |
| `office_admin_agenda` | `/admin/agenda` | GET | `ROLE_STAFF` |
| `office_admin_agenda_book` | `/admin/agenda` | POST | `ROLE_STAFF` |
| `office_admin_agenda_action` | `/admin/agenda/{id}/{action}` | POST | `ROLE_STAFF`; `done`, `no_show`, `cancel`, `confirm`, `move` |
| `office_admin_documents` | `/admin/documents` | GET | `ROLE_STAFF` |
| `office_admin_documents_send` | `/admin/documents` | POST | `ROLE_STAFF` |
| `office_admin_documents_revoke` | `/admin/documents/{id}/revoke` | POST | `ROLE_STAFF` |
| `office_admin_access_log` | `/admin/access-log` | GET | `ROLE_ADMIN` |

The video room's token is 43 characters (`[A-Za-z0-9_-]`). The member CRUD has
an action `registry` (`/{entityId}/registry`) a regime fills in (the Annuaire
Santé for health).

## Twig functions and filters

| Name | Kind | Returns |
|---|---|---|
| `office_main()` | function | the main office |
| `office_offices()` | function | every office, in order |
| `office_team()` | function | the visible members |
| `office_walk_ins()` | function | the active walk-in hours |
| `office_next_slots(member, type, count = 3)` | function | the member's next free slots of that type |
| `office_room(appointment)` | function | the video room of an active video appointment, or null |
| `office_unread_documents(user)` | function | how many documents the user has not opened |
| `office_compliance()` | function | the compliance checks' results |
| `office_person(user)` | function | the member's display name, else the account's |
| `office_layout()` | function | `office.templates.layout` |
| `office_space_nav()` | function | `office.templates.space_nav` |
| `office_timezone()` | function | `office.timezone` |
| `office_document_title` | filter | a document's title, decrypted for whoever may read it |
| `office_document_filename` | filter | its file name |
| `office_reason` | filter | an appointment's reason, decrypted |
| `office_decrypt` | filter | a sealed text, decrypted |
| `office_local` | filter | a date in `office.timezone` |

## Events

| Event | Name | Carries | Listened to by |
|---|---|---|---|
| `AppointmentEvent::BOOKED` | `office.appointment.booked` | the appointment, its cancel token, its series, whether the staff booked it | `BookingMailer` |
| `AppointmentEvent::REQUESTED` | `office.appointment.requested` | the same | `BookingMailer` |
| `AppointmentEvent::CONFIRMED` | `office.appointment.confirmed` | the same | `BookingMailer` |
| `AppointmentEvent::MOVED` | `office.appointment.moved` | the same | `BookingMailer` |
| `AppointmentEvent::CANCELLED` | `office.appointment.cancelled` | the same | `BookingMailer` |
| `AppointmentEvent::REMINDER` | `office.appointment.reminder` | the appointment, the hours before | `BookingMailer` |
| `ClientAccountEvent::CREATED` | `office.client.created` | the user, the invitation | a regime (health makes the patient's profile) |
| `DocumentEvent::DEPOSITED` | `office.document.deposited` | the document, whether to notify | `ShareMailer` |
| `DocumentEvent::REVOKED` | `office.document.revoked` | the document | |

A regime adds its listeners at a higher priority (omnibase/health names the
appointment after the patient's profile on `BOOKED` and `REQUESTED`).

## Commands

| Command | Does |
|---|---|
| `office:booking:remind` | sends the reminders that are due, each once ([Booking](booking.md#cron)) |
| `office:booking:close-day` | closes past appointments (done; a video one never joined: no-show) and purges the closed video rooms |

## Enums

| Enum | Cases |
|---|---|
| `AppointmentStatus` | `requested`, `confirmed`, `cancelled`, `no_show`, `done` |
| `AppointmentSource` | `online`, `phone`, `staff` |
| `BookingMode` | `slots`, `request` |
| `BookableBy` | `everyone`, `known`, `staff` |
| `Channel` | `in_person`, `video`, `home`, `phone` |
| `DocumentKind` | `result`, `prescription`, `report`, `letter`, `upload`, `other` |
| `AccessAction` | `view`, `download`, `upload`, `revoke`, `share`, `denied` |

## Extension points

| Tag or interface | For |
|---|---|
| `office.beneficiary_provider` (`Booking\BeneficiaryProviderInterface`) | whom a client books for besides themselves |
| `Share\AudienceResolverInterface` | who of the practice reads a document, asked by `DocumentVoter` ([Share](share.md#who-may-read)) |
| `ComplianceCheckInterface` | a regime's own checks on the compliance tile (`office_compliance()`) |
| `OfficeLegalSection` | the practice's legal settings on omnibase/admin's settings page |
