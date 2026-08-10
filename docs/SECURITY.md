# Magecko Legacy Security Notes

Magento 2.2 and PHP 7.1 are end-of-life. Magecko Legacy provides functional compatibility only; it does not restore security updates or vendor support to the platform.

## Access Control

Magecko Admin and REST actions use the `Magecko_Blog::posts` ACL resource. Do not assign that resource to users who should not create, edit, delete, or publish blog content.

Magecko does not expose anonymous REST routes.

The storefront is disabled by default. Installation therefore does not claim a public route until an authorized administrator or deploy process enables it.

## Destructive Actions

Admin deletion is POST-only and requires Magento form key validation through the backend controller stack.

## HTML Content

Blog body content is authored by trusted Magento Admin users through Magento's native WYSIWYG editor. Storefront rendering applies Magento CMS directive filtering so media directives render correctly.

Because the body field intentionally stores HTML, access to Magecko editing must be treated like access to CMS page editing. Do not grant blog editing permissions to untrusted users.

## Media Uploads

Magecko validates Admin featured images and REST media uploads before writing files:

- JPG, PNG, GIF, and WebP only.
- 5 MB maximum file size.
- REST uploads validate extension, declared MIME type, and actual image contents.
- Admin featured uploads validate actual image contents before Magento's uploader stores the file.

Files uploaded through Magecko are stored in:

```text
pub/media/magecko/blog
```

Inline images inserted through the WYSIWYG editor use Magento's standard CMS media tooling.

## Production Checklist

- Install on staging first.
- Verify permissions for every Admin role that receives `Magecko_Blog::posts`.
- Verify CSP and WAF/CDN rules match the client storefront.
- Back up the database and `pub/media` before rollout.
- Run `php vendor/contentgecko/magecko-legacy/Test/Integration/run-smoke.php` for Composer installations or `php app/code/Magecko/Blog/Test/Integration/run-smoke.php` for manual installations.
- Confirm draft posts are not accessible on the storefront.
- Confirm REST calls fail without a valid admin or integration token.
- Run `bin/magento magecko:compatibility-check` before enabling the storefront.
