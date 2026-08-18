# Changelog

## 1.0.1 - Magento 2.2 legacy line

- Fixed four ways Magento 2.2.6's Interceptor generator mis-renders PHP 7.1 return and
  parameter types. In developer mode the generated Interceptor mirrors every public method
  of its parent, so any public method using one of these constructs broke the class:
    - Nullable parameter types without a default (`?string $x`) lost the `?`, producing an
      incompatible signature warning.
    - Nullable return types (`: ?int`) lost the `?`, which escalates to a fatal error where
      an interface declares the same method.
    - `void` return types produced `return parent::method();`, a fatal in a void function.
    - `self` return types were rendered as `\self`, an invalid class name.
  All four were removed from public methods and, where applicable, their interfaces. The
  docblocks already carried the same contract. Private and protected methods are not
  mirrored into Interceptors and were left unchanged.
- Replaced the hardcoded landing-page hero text with configurable `Landing Page Heading`
  and `Landing Page Intro` settings, scoped per store view.
- Removed the unused `.magecko-blog-eyebrow` style rule and genericised the API doc examples.
- Fixed the integration smoke runner, which set the area code but never loaded that
  area's DI configuration. Rendering an Admin block in developer mode failed resolving
  area-scoped plugin arguments.

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
