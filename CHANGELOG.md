# Changelog

All notable changes to this project will be documented in this file.

---

## [0.2.0] - 2026-03-06

### Added

- `WizardReceiptInterface` for decoupled receipt handling
- `NotificationDispatcherInterface` for internal notification dispatching
- `WizardSubmissionCreatorInterface`
- `WizardPaymentInitiatorInterface`
- Translated CSV export headers
- UTF-8 BOM support for Excel compatible CSV exports

### Changed

- Refactored CSV export implementation to use explicit field mapping
- Improved testability of application services
- Clarified public API boundary across the bundle
- Marked internal classes with `@internal`

### Fixed

- Duplicate notification emails after submission completion

### Removed

- `WizardNotificationListener` in favor of a single Messenger-based processing pipeline

## [0.1.0] - 2026-03-05
- Initial release with basic form wizard functionality.
