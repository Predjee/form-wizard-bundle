# ADR 002: Attribute-driven autoconfiguration for extensibility points

## Context

The bundle exposes extension points for:

- Field type handlers
- Payment providers

Consumers should be able to add custom implementations without touching the bundle's internal service definitions.

## Decision

We use Symfony attribute-based autoconfiguration:

- `#[AsWizardFieldType]` → tagged as `yiggle_form_wizard.field_type_handler`
- `#[AsPaymentProvider(alias: ...)]` → tagged as `yiggle_form_wizard.payment_provider`

The bundle registers these attributes via `ContainerBuilder::registerAttributeForAutoconfiguration()`.

## Consequences

- Adding a new handler/provider becomes "drop-in": create a class with the attribute.
- The bundle registry discovers implementations via tag iterators.
- We must keep tags stable as part of the public API.
