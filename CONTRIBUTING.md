# Contributing

Thank you for considering contributing!

## Development setup

- PHP 8.2+
- Composer 2

Install dependencies:

```bash
composer install
```

Run quality checks:

```bash
composer qa
```

## Rules

- Keep Domain and Application layers framework-agnostic.
- Prefer attributes over duplicated configuration.
- Add tests for new behavior.
- Keep public APIs stable and document breaking changes.
