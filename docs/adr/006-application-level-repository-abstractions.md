# ADR 006: Application-level repository abstractions

## Context

Application services should be testable without Doctrine and should not depend on Doctrine-specific repository APIs.

Initially, some services depended directly on `ServiceEntityRepository` implementations.

## Decision

We introduce small, explicit interfaces in the Application layer for persistence needs (example: `WizardSubmissionRepositoryInterface`).

Infrastructure repositories implement these interfaces.

## Consequences

- Application services can be unit-tested using mocks/stubs.
- Doctrine remains an implementation detail.
- The public surface is smaller and clearer (only required methods are exposed).
