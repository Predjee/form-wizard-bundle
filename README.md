# Yiggle Form Wizard Bundle

A **Sulu 3** bundle that provides a configurable **multi-step form wizard** with:

- **Sulu Admin** configuration (forms, steps, fields, receivers)
- Website rendering via a wizard controller + Symfony Forms
- Optional **payment provider integration** (e.g. Mollie)
- Email notifications (admin and customer)
- Export of submissions

> Status: pre-1.0. The API is intended to be stable, but namespaces and extension points may still evolve.

## Compatibility

| Component | Supported |
|---|---|
| PHP | 8.4+ |
| Symfony | 7.4 / 8.x |
| Sulu | 3.x |
| Doctrine ORM | 2.20+ / 3.x |

## Installation

```bash
composer require yiggle/form-wizard-bundle
```

Enable the bundle (usually not needed with Symfony Flex):

```php
// config/bundles.php
return [
    Yiggle\FormWizardBundle\YiggleFormWizardBundle::class => ['all' => true],
];
```

### Database tables

This bundle ships Doctrine entities and expects your project to manage migrations.

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## Configuration

```yaml
# config/packages/yiggle_form_wizard.yaml
yiggle_form_wizard:
  notifiers:
    email:
      default_from_email: 'noreply@example.com'
      default_from_name: 'Form Wizard'

  payment:
    mollie:
      enabled: true
      api_key: '%env(MOLLIE_API_KEY)%'
      # If you need public URLs in non-public environments (proxies / load balancers), set these:
      webhook_url_base: '%env(resolve:FW_WEBHOOK_URL_BASE)%'
```

## Usage

### Admin

The bundle registers a Sulu Admin module to manage:

- Wizards (forms)
- Steps
- Fields + step-field configuration (required, widths, base price, etc.)
- Receivers + email templates

### Website controller

The bundle provides a website wizard controller to render and process multi-step forms.

Routes are provided by the bundle. In a Sulu project, they are typically imported automatically; otherwise import them:

```yaml
# config/routes/yiggle_form_wizard.yaml
yiggle_form_wizard:
  resource: '@YiggleFormWizardBundle/config/routes.yaml'
```

## Extension points

### 1) Field types

Implement a field type handler:

```php
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AsWizardFieldType;
use Yiggle\FormWizardBundle\Domain\Contract\FieldType\WizardFieldTypeHandlerInterface;

#[AsWizardFieldType]
final class MyFieldTypeHandler implements WizardFieldTypeHandlerInterface
{
    public function getKey(): string { return 'my_type'; }

    public function buildForm(/* ... */): void
    {
        // build Symfony form field
    }
}
```

The bundle automatically tags handlers via `#[AsWizardFieldType]` and discovers them through the registry.

If your field type affects pricing, implement:

- `PriceAwareFieldTypeHandlerInterface`
- `ReceiptAwareFieldTypeHandlerInterface`

### 2) Payment providers

Implement the provider contract:

```php
use Yiggle\FormWizardBundle\Infrastructure\Payment\Attribute\AsPaymentProvider;
use Yiggle\FormWizardBundle\Domain\Contract\Payment\PaymentProviderInterface;

#[AsPaymentProvider(alias: 'acme')]
final class AcmeProvider implements PaymentProviderInterface
{
    public function getAlias(): string { return 'acme'; }
    public function isEnabled(): bool { return true; }

    public function startPayment(WizardSubmission $submission): ?string
    {
        // set paymentReference/provider on $submission if applicable
        // return checkout URL
    }

    public function fetchStatus(string $transactionId): PaymentStatus
    {
        // query provider and map to PaymentStatus
    }
}
```

### 3) Notifications

Implement `WizardNotifierInterface` and tag it:

```php
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Yiggle\FormWizardBundle\Domain\Contract\WizardNotifierInterface;

#[AutoconfigureTag('yiggle_form_wizard.wizard_notifier')]
final class MyNotifier implements WizardNotifierInterface
{
    // ...
}
```

## Events

The bundle dispatches Symfony events for integration:

- `WizardSubmissionCreatedEvent`
- `WizardPaymentInitiatedEvent`
- `WizardPaymentFailedEvent`
- `WizardSubmissionCompletedEvent`

Use these to hook into custom workflows (CRM sync, analytics, webhooks, etc.).

## Development

```bash
composer install
composer qa
```

### QA tools

- `ecs` (coding standards)
- `phpstan`
- `rector`
- `phpunit`

## Architectural Decision Records

See `docs/adr/`.

## License

MIT
