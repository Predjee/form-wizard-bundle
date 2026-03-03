# ADR 004: Symfony events as the primary integration surface

## Context

Projects often want to react to:

- submission creation
- payment initiation or failure
- submission completion

Hard-coding integrations (CRM, analytics, ERP) into the bundle would make it less reusable.

## Decision

We dispatch Symfony events:

- `WizardSubmissionCreatedEvent`
- `WizardPaymentInitiatedEvent`
- `WizardPaymentFailedEvent`
- `WizardSubmissionCompletedEvent`

## Consequences

- Integrations can be implemented as listeners/subscribers in the host project.
- Events become part of the public API and must remain stable.
- Internal flows should always dispatch these events consistently.
