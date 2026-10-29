---
title: "Configuration reference"
order: 40
---

# Configuration reference

## The `office` tree

Dumped with Symfony's `YamlReferenceDumper` from
`Base\Office\DependencyInjection\OfficeConfiguration`. The keys are
explained where they act: the practice's words and templates in
[omnibase/office](index.md#configuration), the booking in
[Booking](booking.md), the vault in [Share](share.md), the video room and its
gateway in [Visio](visio.md).

```yaml
office:

    # The office's timezone: schedules are written in it, slots are given in it.
    timezone:             Europe/Paris

    # Where a contact request is sent.
    recipient:            '%env(MAILER_CONTACT)%'

    # The From of the e-mails the office sends (confirmations, reminders, "a document awaits").
    sender:               '%env(MAILER_TECHNICAL)%'

    # How the practice is named in the bundle's texts ({the_practice}, {at_the_practice}, {of_the_practice}...). practice: cabinet | etude | office | maison_de_sante, or its forms: {practice: {name: centre de soins, the: le centre de soins, at: au centre de soins, of: du centre de soins}}.
    vocabulary:

        # Default:
        practice:            cabinet
    contact:

        # The warning above the contact form, a translation key: the regime chooses it (health: no medical data here, use your space).
        notice:               '@office.contact.notice'
        retention_months:     12

    # The public pages, a regime may give its own.
    templates:
        team:                 '@Office/client/team.html.twig'
        member:               '@Office/client/member.html.twig'
        hours:                '@Office/client/hours.html.twig'
        contact:              '@Office/client/contact.html.twig'

        # The page of a first appointment asked without an account (booking.public_request).
        request:              '@Office/client/request.html.twig'

        # A partial included at the top of the client's pages (appointments, documents): the regime's menu of the space.
        space_nav:            null

        # The template the pages extend.
        layout:               base.html.twig
    booking:
        enabled:              true

        # Opens /rendez-vous/demande (office_request): a first appointment asked without an account, registered in request mode. A regime with its own page (notary, lawyer) does not need it.
        public_request:       false

        # Minutes: a slot closer than this cannot be booked online.
        min_notice:           120

        # Days ahead a slot can be booked online.
        horizon:              60

        # Hours before the appointment the client may still cancel online.
        cancel_until:         24

        # Minutes between two slot starts; 0: the appointment type's duration plus its buffer.
        step:                 0

        # When reminders are sent: hours before the appointment.
        reminders:

            # Defaults:
            - 24
            - 2

        # The client's list of appointments, linked from the e-mails.
        space_route:          office_space_appointments
    share:
        enabled:              true

        # The Flysystem storage the encrypted files are written to: private, never under public/.
        storage:              local.share

        # Base64 of 32 bytes, from the secrets vault. Missing: nothing is stored or read (fail closed).
        master_key:           '%env(default::SHARE_MASTER_KEY)%'
        max_size:             20971520
        mime_types:

            # Defaults:
            - application/pdf
            - image/jpeg
            - image/png
            - image/webp
            - image/heic
            - text/plain

        # Where the "a document awaits" e-mail sends the recipient (sign-in required).
        space_route:          office_space_documents
    visio:
        enabled:              true

        # Minutes before the appointment the room opens.
        open_before:          10

        # Minutes after its planned end the room closes.
        close_after:          30

        # What carries the calls: the name of a glitchr/omnimeet gateway (omnimeet.gateways.<name>). "direct" - from browser to browser, omnimeet/direct - is declared for you.
        gateway:              direct

        # Whose name a gateway is given, to show the other: the host's - the member's, already public on the site - or none. The client's is never given: a provider would learn who consults.
        names:                host # One of "host"; "none"

        # Deprecated alias of omnimeet.gateways.direct.options.turn_secret - coturn's static-auth-secret: the temporary TURN credentials are signed with it.
        turn_secret:          '%env(default::TURN_SECRET)%'

        # Deprecated alias of omnimeet.gateways.direct.options.turn_urls.
        turn_urls:            []

        # Deprecated alias of omnimeet.gateways.direct.options.stun_urls.
        stun_urls:            []

        # Deprecated alias of omnimeet.gateways.direct.options.turn_ttl.
        turn_ttl:             3600

        # host:port the compliance check asks instead of the first TURN URL, when the server does not reach the relay by the address browsers use (a container's service name).
        turn_probe:           null
```

Each key is a container parameter (`%office.timezone%`,
`%office.templates.layout%`, `%office.booking.space_route%`...).
