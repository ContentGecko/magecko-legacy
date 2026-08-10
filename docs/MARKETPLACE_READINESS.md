# Legacy Release Readiness

This package is a client compatibility build for Magento 2.2.6, not a new Adobe Commerce Marketplace submission.

## Completed Validation

- PHP 7.1.33 syntax lint for all PHP and PHTML files
- Magento Open Source 2.2.6 installation from an empty MySQL 5.7 database
- `Magecko_Blog` legacy schema installation
- Magento dependency-injection compilation
- Magento static content deployment for Admin, Blank, and Luma
- `magecko:compatibility-check`
- Repository, Admin rendering, WYSIWYG, pagination, filtering, and media smoke tests
- Public blog list and post rendering over HTTP

## Release Boundaries

- The Composer package name is `contentgecko/magecko-legacy`.
- The Magento module name remains `Magecko_Blog`.
- The modern `contentgecko/magecko` package conflicts with this package.
- Compatibility claims are limited to Magento 2.2.6, framework 101.0.6, and PHP 7.1.x.
- Magento 2.2 and PHP 7.1 remain end-of-life even when this module is installed.
