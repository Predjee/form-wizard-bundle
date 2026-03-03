# ADR 003: Registry pattern for field types and payment providers

## Context

Field type handlers and payment providers are pluggable.

We need:

- deterministic lookup by key/alias
- a single place to validate duplicates
- an easy way to list enabled providers for the Admin UI

## Decision

We use registry services:

- `WizardFieldTypeRegistry` (key → handler)
- `PaymentProviderRegistry` (alias → provider)

The registries are built using tagged iterators and validate key uniqueness.

## Consequences

- Extension is open: adding new implementations does not require changes to core code.
- Core code stays simple: it asks the registry for a handler/provider.
- Duplicate keys are detected early (container compilation or first access).
