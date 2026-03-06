# ADR 007 — Public API boundary and notification processing

## Status

Accepted

## Context

The FormWizard bundle evolved into a layered architecture with domain models, application orchestration services, infrastructure adapters and Symfony integrations.

Over time two architectural concerns emerged:

1. The boundary between public extension points and internal implementation details was not clearly documented.
2. Submission completion triggered notifications through two internal paths:
    - a Symfony event listener
    - a Messenger message handler

This caused duplicate notifications and made the processing model harder to reason about.

## Decision

### Public API boundary

The bundle explicitly defines a public extension surface.

Public API includes:

- integration events
- domain models
- provider contracts intended for extension

All other classes are considered internal.

Internal classes are marked using:

@internal

### Notification processing model

Submission completion now follows a single processing pipeline.

When a submission completes:

1. `WizardSubmissionCompletedEvent` is dispatched
2. `ProcessSubmission` is sent to Messenger
3. `ProcessSubmissionHandler` performs internal side effects

This ensures that internal processing happens exactly once.

### Internal orchestration interfaces

To improve testability, several orchestration dependencies are abstracted behind interfaces:

- `NotificationDispatcherInterface`
- `WizardSubmissionCreatorInterface`
- `WizardPaymentInitiatorInterface`

These interfaces are internal contracts used for decoupling and testing.

## Consequences

Positive:

- duplicate notification dispatching resolved
- clearer public API boundary
- easier testing of orchestration services
- safer future refactoring

Negative:

- some interfaces remain public in PHP visibility but should still be treated as internal.
