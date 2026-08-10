# Changelog

## 1.0.0 - Magento 2.2 legacy line

- Created a separate Composer package, `contentgecko/magecko-legacy`.
- Pinned dependencies to Magento 2.2.6 component versions and PHP 7.1.x.
- Replaced declarative schema with Magento 2.2 `InstallSchema` setup code.
- Replaced newer HTTP action interfaces with Magento 2.2 action controllers.
- Backported production and test code to PHP 7.1 syntax.
- Replaced the newer template escaper variable with Magento 2.2 block escaping.
- Switched the Admin editor configuration to Magento 2.2's native TinyMCE provider.
- Updated the compatibility command for the exact legacy stack.
- Added Admin template rendering to the integration smoke test.
- Validated installation, database schema, DI compilation, static content deployment, CLI compatibility checks, integration smoke tests, and storefront rendering on Magento Open Source 2.2.6 with PHP 7.1.33.
