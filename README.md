# Yiggle Form Wizard Bundle

[![CI](https://github.com/Predjee/form-wizard-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/Predjee/form-wizard-bundle/actions)
[![Latest Version](https://img.shields.io/packagist/v/yiggle/form-wizard-bundle.svg)](https://packagist.org/packages/yiggle/form-wizard-bundle)
[![Downloads](https://img.shields.io/packagist/dt/yiggle/form-wizard-bundle.svg)](https://packagist.org/packages/yiggle/form-wizard-bundle)
[![License](https://img.shields.io/github/license/Predjee/form-wizard-bundle.svg)](LICENSE)

## Concept

The bundle implements a **content-driven form workflow engine**.

A wizard consists of:

```
Wizard → Steps → Fields → Submission
```

Optionally a wizard may include:

- Payment handling
- Notifications
- Export integrations

The runtime flow is powered by **Symfony Form Flow** while the configuration is managed via **Sulu Admin blocks**.

A **Sulu 3** bundle that provides a configurable **multi-step form wizard** with:

- **Sulu Admin** configuration (forms, steps, fields, receivers)
- Website rendering via a wizard controller + Symfony Forms
- Optional **payment provider integration** (e.g. Mollie)
- Email notifications (admin and customer)
- Export of submissions

> **Status: pre-1.0.** The public API is intended to be stable, but namespaces and extension points may still evolve.

---

## Compatibility

| Component    | Supported      |
|--------------|----------------|
| PHP          | 8.4+           |
| Symfony      | 7.4 / 8.x      |
| Sulu         | 3.x            |
| Doctrine ORM | 2.20+ / 3.x    |

---

## Installation

### 1. Enable the Yiggle recipe repository

The bundle ships its Symfony Flex recipes in a custom repository.

```bash
composer config --no-plugins --json extra.symfony.endpoint \
'["https://api.github.com/repos/Predjee/symfony-recipes/contents/index.json?ref=main","flex://defaults"]'
```

### 2. Install the bundle

```bash
composer require yiggle/form-wizard-bundle
```

The Flex recipe will automatically install:

- Routes
- Default configuration
- Post-install instructions

---

## Database

The bundle ships Doctrine entities and expects your project to manage migrations.

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

---

## Sulu Admin Integration

The bundle provides a Sulu admin module to manage:

- Wizards (forms)
- Steps
- Fields
- Email receivers
- Submissions export

Import the bundle's admin JavaScript in `assets/admin/app.js`:

```javascript
import '@yiggle-form-wizard/admin/index.js';
```

Add the alias to your admin webpack config (`assets/admin/webpack.config.js`):

```javascript
config.resolve.alias = {
    ...config.resolve.alias,
    '@yiggle-form-wizard': path.resolve(__dirname, '..', '..', 'vendor', 'yiggle', 'form-wizard-bundle', 'assets'),
};
```

Rebuild the Sulu admin interface:

```bash
php bin/console sulu:admin:update-build
```

> **Note:** The admin build output (`public/build/admin`) should be committed to your repository. Answer `n` when prompted to overwrite `webpack.config.js` and `package.json` during the build.

---

## Frontend Assets

The bundle ships optional frontend helpers.

### CSS

An optional stylesheet is available:

```javascript
import '@yiggle-form-wizard/styles/form_wizard.css';
```

Import it in your frontend build (Webpack, Vite, AssetMapper, etc.).

### Stimulus controller (optional)

For AJAX-based wizard flows using Turbo.

Requires:

```bash
composer require symfony/stimulus-bundle symfony/ux-turbo
```

Enable the controller in your `assets/controllers.json`:

```json
{
    "controllers": {
        "@yiggle/form-wizard-bundle": {
            "receipt-trigger": {
                "enabled": true
            }
        }
    }
}
```

---

## Configuration

```yaml
# config/packages/yiggle_form_wizard.yaml

yiggle_form_wizard:
    notifiers:
        email:
            default_from_email: '%env(default::YIGGLE_FORM_WIZARD_DEFAULT_FROM_EMAIL)%'
            default_from_name: '%env(default::YIGGLE_FORM_WIZARD_DEFAULT_FROM_NAME)%'

    payment:
        mollie:
            enabled: '%env(bool:default::YIGGLE_FORM_WIZARD_MOLLIE_ENABLED)%'
            api_key: '%env(default::YIGGLE_FORM_WIZARD_MOLLIE_API_KEY)%'
            webhook_url_base: '%env(default::YIGGLE_FORM_WIZARD_MOLLIE_WEBHOOK_URL_BASE)%'
```

Optional environment variables:

```
YIGGLE_FORM_WIZARD_DEFAULT_FROM_EMAIL
YIGGLE_FORM_WIZARD_DEFAULT_FROM_NAME

YIGGLE_FORM_WIZARD_MOLLIE_ENABLED
YIGGLE_FORM_WIZARD_MOLLIE_API_KEY
YIGGLE_FORM_WIZARD_MOLLIE_WEBHOOK_URL_BASE
```

---

## Extension Points

The bundle is designed to be extensible without modifying core code. See [docs/extension-points.md](docs/extension-points.md) for full documentation.

### Field Types

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

### Payment Providers

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
        // return checkout URL
    }

    public function fetchStatus(string $transactionId): PaymentStatus
    {
        // query provider
    }
}
```

### Notifications

```php
#[AutoconfigureTag('yiggle_form_wizard.wizard_notifier')]
final class MyNotifier implements WizardNotifierInterface
{
}
```

---

## Events

The bundle dispatches Symfony events throughout the wizard lifecycle. See [docs/payment-lifecycle.md](docs/payment-lifecycle.md) for the full flow.

| Event | Description |
|-------|-------------|
| `WizardSubmissionCreatedEvent` | Dispatched when a user starts a new wizard |
| `WizardPaymentInitiatedEvent` | Dispatched when the user is redirected to a PSP |
| `WizardPaymentFailedEvent` | Dispatched if a payment transaction fails |
| `WizardSubmissionCompletedEvent` | Dispatched when the wizard is fully finished |

---

## Development

```bash
composer install
composer qa
```

QA tools include:

- ECS
- PHPStan
- Rector
- PHPUnit

---

## Documentation

- [Mental Model](docs/mental-model.md)
- [Extension Points](docs/extension-points.md)
- [Payment Lifecycle](docs/payment-lifecycle.md)
- [Architectural Decision Records](docs/adr/)

---

## License

MIT
