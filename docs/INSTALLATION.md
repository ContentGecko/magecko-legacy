# Magecko Legacy Installation Guide

## Exact Requirements

- Magento Open Source or Magento Commerce 2.2.6
- `magento/framework` 101.0.6
- PHP 7.1.x
- Composer 1.x
- Database and `pub/media` backups
- CLI access as the Magento filesystem owner

Do not install `contentgecko/magecko-legacy` together with `contentgecko/magecko`.

## Composer Artifact Installation

Copy `contentgecko-magecko-legacy-1.0.0.zip` into a directory readable by the Magento filesystem owner. Configure that directory as a Composer artifact repository:

```bash
composer config repositories.magecko-legacy artifact /absolute/path/to/magecko-artifacts
composer require contentgecko/magecko-legacy:1.0.0
```

Complete the Magento installation:

```bash
bin/magento module:enable Magecko_Blog
bin/magento setup:upgrade
bin/magento magecko:compatibility-check
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
bin/magento cache:flush
```

## Manual Installation

Extract or copy the package contents into:

```text
app/code/Magecko/Blog
```

Run:

```bash
bin/magento module:enable Magecko_Blog
bin/magento setup:upgrade
bin/magento magecko:compatibility-check
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
bin/magento cache:flush
```

## Post-install Checks

Magecko's storefront is disabled by default. Before enabling it:

```bash
bin/magento magecko:compatibility-check
```

Open `Stores > Configuration > General > Magecko Blog`, choose an unused route, and enable the storefront only for the intended scope. Alternatively:

```bash
bin/magento config:set magecko_blog/storefront/route blog
bin/magento config:set magecko_blog/storefront/enabled 1
bin/magento cache:flush
bin/magento magecko:compatibility-check
```

Run the package smoke test:

```bash
php vendor/contentgecko/magecko-legacy/Test/Integration/run-smoke.php
```

For a manual installation:

```bash
php app/code/Magecko/Blog/Test/Integration/run-smoke.php
```

Then verify:

- `Content > Elements > Blog Posts` opens.
- A post can be saved with the WYSIWYG editor.
- Draft posts return a storefront 404.
- Published posts render under the configured route.
- Featured images write to `pub/media/magecko/blog`.
- API routes reject unauthenticated requests.

## Upgrade Within the Legacy Line

Keep the package on the `1.x` legacy line:

```bash
composer update contentgecko/magecko-legacy
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
bin/magento cache:flush
```

Do not replace it in place with the modern Magecko package. Moving to the modern package requires upgrading Magento and PHP first and should be handled as a planned migration.

## Disable

```bash
bin/magento module:disable Magecko_Blog
bin/magento cache:flush
```

Disabling does not delete the `magecko_blog_post` or `magecko_blog_post_store` tables and does not delete media.
