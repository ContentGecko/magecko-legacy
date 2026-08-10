# Magecko Legacy for Magento 2.2.6

This is the isolated legacy build of Magecko for stores that cannot upgrade from Magento 2.2.6 and PHP 7.1.

Do not install this package together with the modern `contentgecko/magecko` package. Both packages provide the same Magento module, `Magecko_Blog`, and the legacy package declares an explicit Composer conflict to prevent that combination.

## Supported Stack

- PHP 7.1.x; validated on PHP 7.1.33
- Magento Open Source or Magento Commerce 2.2.6
- `magento/framework` 101.0.6
- MySQL 5.7-compatible database
- Composer 1.x as required by the Magento 2.2 application

This package intentionally uses Magento 2.2 `InstallSchema` setup code, legacy action controllers, the Magento 2.2 TinyMCE integration, and PHP 7.1-compatible syntax. It does not contain `db_schema.xml`, data patches, typed properties, attributes, arrow functions, or the newer HTTP action interfaces.

## Features

- Admin post creation, editing, deletion, filtering, and pagination
- Draft and published statuses
- Magento 2.2 native WYSIWYG editor
- Featured and inline article images
- SEO title, description, canonical URL, and hreflang output
- Store-view translations
- Configurable storefront route, disabled by default
- Authenticated REST API
- Compatibility and route-conflict checks

## Installation

Back up the database and `pub/media` and deploy to staging first.

For the supplied Composer artifact, place the ZIP in a local artifact directory and run:

```bash
composer config repositories.magecko-legacy artifact /absolute/path/to/magecko-artifacts
composer require contentgecko/magecko-legacy:1.0.0
bin/magento module:enable Magecko_Blog
bin/magento setup:upgrade
bin/magento magecko:compatibility-check
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
bin/magento cache:flush
```

For a manual installation, copy this package's contents to:

```text
app/code/Magecko/Blog
```

Then run the Magento commands above, omitting the Composer repository and `composer require` commands.

## Enabling the Storefront

The public route is disabled after installation so it cannot unexpectedly take ownership of `/blog`.

Run:

```bash
bin/magento magecko:compatibility-check
```

Then configure `Stores > Configuration > General > Magecko Blog`, select an unused route, and enable the storefront for the intended scope. The equivalent CLI commands are:

```bash
bin/magento config:set magecko_blog/storefront/route blog
bin/magento config:set magecko_blog/storefront/enabled 1
bin/magento cache:flush
bin/magento magecko:compatibility-check
```

## Validation

From the Magento root:

```bash
php vendor/contentgecko/magecko-legacy/Test/Integration/run-smoke.php
```

For a manual installation:

```bash
php app/code/Magecko/Blog/Test/Integration/run-smoke.php
```

The smoke test checks repository writes, published filtering, pagination, Admin listing/editor rendering, Magento 2.2 WYSIWYG setup, route configuration, and image validation.

## API

All routes require an Admin or integration bearer token with `Magecko_Blog::posts` permission:

```text
GET    /rest/V1/magecko-blog/posts
GET    /rest/V1/magecko-blog/posts/:postId
GET    /rest/V1/magecko-blog/posts/:postId/store/:storeId
GET    /rest/V1/magecko-blog/posts/slug/:slug
GET    /rest/V1/magecko-blog/posts/store/:storeId/slug/:slug
POST   /rest/V1/magecko-blog/posts
PUT    /rest/V1/magecko-blog/posts/:postId
PUT    /rest/V1/magecko-blog/posts/:postId/store/:storeId
DELETE /rest/V1/magecko-blog/posts/:postId
DELETE /rest/V1/magecko-blog/posts/:postId/store/:storeId
POST   /rest/V1/magecko-blog/media
```

See `docs/API.md` for payloads.

## Security Status

Magento 2.2 and PHP 7.1 are end-of-life. This package provides functional compatibility but cannot restore vendor security support to the underlying platform. Restrict Admin and API access, keep the store behind compensating controls, and treat an eventual platform upgrade as a separate security requirement.

See `docs/INSTALLATION.md`, `docs/SECURITY.md`, and `docs/USER_GUIDE.md` for operational details.
