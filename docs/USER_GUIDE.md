# Magecko User Guide

## Open Blog Posts

In Magento Admin, go to:

```text
Content > Elements > Blog Posts
```

The list shows post ID, title, status, slug, category, author, publish date, modified date, and actions. Use the filters above the table to find posts by title, status, category, or author.

## Create a Post

1. Click `Add New Post`.
2. Enter title, slug, status, category, author, publish date, and modified date.
3. Upload an optional featured image.
4. Add featured image alt text.
5. Fill SEO fields if the defaults are not enough.
6. Write the article in the WYSIWYG editor.
7. Save as `Draft` or `Published`.

Draft posts are visible in Admin only. Published posts appear only when the Magecko storefront is enabled.

## Storefront Configuration

Go to `Stores > Configuration > General > Magecko Blog`.

- `Enable Storefront` controls whether Magecko claims its configured frontend route.
- `Frontend Route` controls the landing-page URL key, for example `magecko-test` or `articles`.
- `Landing Page Heading` sets the H1 on the blog landing page. Defaults to `Blog`.
- `Landing Page Intro` is an optional paragraph below the heading. Leave it empty to hide it.

Settings can differ by website or store view. Leave Magecko disabled until the route has passed `bin/magento magecko:compatibility-check` on staging.

## Categories and Authors

Magecko stores category and author values directly on posts. Existing values appear as suggestions in the Admin form, and new values can be typed directly into the same fields.

## Images

Featured images can be uploaded on the post edit screen. Magecko accepts JPG, PNG, GIF, and WebP images up to 5 MB.

Inline article images should be inserted through the WYSIWYG editor media tools. These images use Magento's standard CMS media browser and storage.

## Store View Translations

After the base post is saved, translation panels appear for non-default store views. Translate the fields needed for that store view:

- Title
- Slug
- Category
- Author
- Featured image alt text
- Meta title
- Meta description
- Canonical URL
- Body HTML

Leave a translated field blank to fall back to the base post value where applicable.

## SEO

Magecko outputs:

- Page title from meta title, falling back to post title.
- Meta description when set.
- Canonical link from the canonical URL field, falling back to the current post URL.
- Hreflang alternate links for translated store-view URLs.

Use canonical URL overrides only when a post should explicitly point to another canonical URL.

## Storefront

The blog landing page is available under the configured route, for example:

```text
/magecko-test
```

Post pages are available at:

```text
/magecko-test/{slug}
```

The blog list is paginated and only displays published posts.
