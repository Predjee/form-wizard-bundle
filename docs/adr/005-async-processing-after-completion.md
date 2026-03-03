# ADR 005: Asynchronous processing after completion

## Context

After a submission is completed we may want to:

- send emails
- export
- trigger integrations

These actions can be slow and should not block the user.

## Decision

After completion we dispatch a Messenger message (`ProcessSubmission`) so that email/integration work can be handled asynchronously.

If the host project does not configure transports, the message will still be handled synchronously (default behavior), but the architecture remains ready for async.

## Consequences

- The website UX stays fast.
- Projects can configure a queue later without changing business logic.
- The message payload remains small and stable (submission UUID).
