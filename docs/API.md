# Magecko API Guide

All Magecko REST endpoints require a Magento admin or integration bearer token with access to `Magecko_Blog::posts`.

## Authentication

Create an admin token:

```bash
curl -X POST https://example.com/rest/V1/integration/admin/token \
  -H 'Content-Type: application/json' \
  -d '{"username":"admin","password":"password"}'
```

Use the returned token:

```bash
Authorization: Bearer TOKEN
```

## Endpoints

```text
GET    /rest/V1/magecko-blog/posts
GET    /rest/V1/magecko-blog/posts/{postId}
GET    /rest/V1/magecko-blog/posts/{postId}/store/{storeId}
GET    /rest/V1/magecko-blog/posts/slug/{slug}
GET    /rest/V1/magecko-blog/posts/store/{storeId}/slug/{slug}
POST   /rest/V1/magecko-blog/posts
PUT    /rest/V1/magecko-blog/posts/{postId}
PUT    /rest/V1/magecko-blog/posts/{postId}/store/{storeId}
DELETE /rest/V1/magecko-blog/posts/{postId}
DELETE /rest/V1/magecko-blog/posts/{postId}/store/{storeId}
POST   /rest/V1/magecko-blog/media
```

## Post Payload

```json
{
  "post": {
    "title": "Brake Pad Wear: What to Check Before Every Ride",
    "slug": "brake-pad-wear-checklist",
    "status": "published",
    "topic": "Brakes",
    "author": "Magecko",
    "publish_date": "2026-07-06 13:00:00",
    "modified_date": "2026-07-06 13:00:00",
    "featured_image": "magecko/blog/brake-pads.png",
    "featured_image_alt": "Product photo on a neutral background",
    "meta_title": "Brake Pad Wear Checklist",
    "meta_description": "A practical inspection and maintenance guide.",
    "canonical_url": "",
    "body_html": "<h2>Brake pad inspection</h2><p>Check pad material before long rides.</p>"
  }
}
```

`status` accepts `draft` or `published`. Draft posts are not shown on the storefront.

## Media Upload

Upload base64 media:

```json
{
  "image": {
    "file_name": "brake-pads.png",
    "content_base64": "BASE64_CONTENT_HERE",
    "mime_type": "image/png"
  }
}
```

Media upload restrictions:

- JPG, PNG, GIF, and WebP only.
- Maximum size is 5 MB.
- File extension, declared MIME type, and actual image contents must match.

The response contains a relative media path and absolute URL:

```json
{
  "path": "magecko/blog/brake-pads.png",
  "url": "https://example.com/media/magecko/blog/brake-pads.png"
}
```

Use `path` as a post `featured_image`.

## Search Criteria

`GET /rest/V1/magecko-blog/posts` supports Magento search criteria query parameters, for example:

```text
/rest/V1/magecko-blog/posts?searchCriteria[filterGroups][0][filters][0][field]=status&searchCriteria[filterGroups][0][filters][0][value]=published
```
