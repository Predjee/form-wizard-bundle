# ADR 001: Layered architecture (Domain / Application / Infrastructure / Presentation)

## Context

This bundle is intended to be reusable across Sulu projects and should remain extensible without forcing consumers to fork or patch internal classes.

At the same time, the bundle contains multiple concerns:

- Domain concepts (payment status, receipts)
- Application orchestration (wizard completion, status processing)
- Infrastructure integrations (Doctrine, Symfony events, Sulu Admin)
- Web presentation (controllers, Symfony form types)

Without a clear structure, these concerns tend to become coupled and hard to evolve.

## Decision

We split the codebase into four layers:

- **Domain**: pure business concepts and contracts, independent of framework and persistence
- **Application**: use-cases / orchestration services that depend on Domain contracts
- **Infrastructure**: adapters for Doctrine, Symfony, Sulu, external providers
- **Presentation**: controllers and form types for the website layer

Dependencies are intended to flow inward:

`Presentation → Application → Domain`

`Infrastructure → Domain` (and provides implementations for Application abstractions)

## Consequences

- Application services should not depend directly on Doctrine repositories; they should depend on Application-level interfaces.
- Infrastructure can evolve (e.g. different persistence or provider implementations) without breaking Domain/Application.
- Refactors are safer: responsibilities remain local and testable.
