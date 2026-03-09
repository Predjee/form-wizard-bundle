# Extension Points

The Yiggle Form Wizard Bundle is designed to be extensible without modifying core code. Developers can extend the system through Field Types, Payment Providers, Notifications, and Events.

---

## Field Types

Field types define how input is collected and processed. Register a field type using the `#[AsWizardFieldType]` attribute. Your class must implement the `WizardFieldTypeHandlerInterface`.

**Example:**

```php
#[AsWizardFieldType]
final class MyFieldTypeHandler implements WizardFieldTypeHandlerInterface
{
    public function getKey(): string
    {
        return 'my_type';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Build Symfony form field
    }
}
```

**Use cases:**
- Custom input components
- Dynamic participant lists
- Advanced validation

---

## Payment Providers

Payment providers integrate external PSP systems. Implement the `PaymentProviderInterface` and use the `#[AsPaymentProvider]` attribute.

**Example:**

```php
#[AsPaymentProvider(alias: 'acme')]
final class AcmeProvider implements PaymentProviderInterface
{
    public function getAlias(): string
    {
        return 'acme';
    }

    public function startPayment(WizardSubmission $submission): ?string
    {
        return 'https://checkout.example';
    }

    public function fetchStatus(string $transactionId): PaymentStatus
    {
        // Query PSP
    }
}
```

---

## Notifications

Notifications allow integrations with external systems. Register a notifier using the tag: `yiggle_form_wizard.wizard_notifier`.

**Use cases:**
- Email confirmations
- CRM integrations
- Webhook triggers

---

## Events

The bundle dispatches standard Symfony events throughout the wizard lifecycle:

| Event | Description |
|-------|-------------|
| `WizardSubmissionCreatedEvent` | Dispatched when a user starts a new wizard. |
| `WizardPaymentInitiatedEvent` | Dispatched when the user is redirected to a PSP. |
| `WizardPaymentFailedEvent` | Dispatched if a payment transaction fails. |
| `WizardSubmissionCompletedEvent` | Dispatched when the wizard is fully finished. |

These allow developers to hook into the lifecycle without modifying the core bundle.
