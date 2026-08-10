<?php
declare(strict_types=1);

namespace Magecko\Blog\Api\Data;

interface PostInterface
{
    public const POST_ID = 'post_id';
    public const TITLE = 'title';
    public const SLUG = 'slug';
    public const STATUS = 'status';
    public const TOPIC = 'topic';
    public const AUTHOR = 'author';
    public const PUBLISH_DATE = 'publish_date';
    public const MODIFIED_DATE = 'modified_date';
    public const FEATURED_IMAGE = 'featured_image';
    public const FEATURED_IMAGE_ALT = 'featured_image_alt';
    public const META_TITLE = 'meta_title';
    public const META_DESCRIPTION = 'meta_description';
    public const CANONICAL_URL = 'canonical_url';
    public const BODY_HTML = 'body_html';
    public const STORE_ID = 'store_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * @return int|null
     */
    public function getPostId(): ?int;

    /**
     * @param int|null $postId
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setPostId(?int $postId): PostInterface;

    /**
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * @param string|null $title
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setTitle(?string $title): PostInterface;

    /**
     * @return string|null
     */
    public function getSlug(): ?string;

    /**
     * @param string|null $slug
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setSlug(?string $slug): PostInterface;

    /**
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * @param string|null $status
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setStatus(?string $status): PostInterface;

    /**
     * @return string|null
     */
    public function getTopic(): ?string;

    /**
     * @param string|null $topic
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setTopic(?string $topic): PostInterface;

    /**
     * @return string|null
     */
    public function getAuthor(): ?string;

    /**
     * @param string|null $author
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setAuthor(?string $author): PostInterface;

    /**
     * @return string|null
     */
    public function getPublishDate(): ?string;

    /**
     * @param string|null $publishDate
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setPublishDate(?string $publishDate): PostInterface;

    /**
     * @return string|null
     */
    public function getModifiedDate(): ?string;

    /**
     * @param string|null $modifiedDate
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setModifiedDate(?string $modifiedDate): PostInterface;

    /**
     * @return string|null
     */
    public function getFeaturedImage(): ?string;

    /**
     * @param string|null $featuredImage
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setFeaturedImage(?string $featuredImage): PostInterface;

    /**
     * @return string|null
     */
    public function getFeaturedImageAlt(): ?string;

    /**
     * @param string|null $featuredImageAlt
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setFeaturedImageAlt(?string $featuredImageAlt): PostInterface;

    /**
     * @return string|null
     */
    public function getMetaTitle(): ?string;

    /**
     * @param string|null $metaTitle
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setMetaTitle(?string $metaTitle): PostInterface;

    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string;

    /**
     * @param string|null $metaDescription
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setMetaDescription(?string $metaDescription): PostInterface;

    /**
     * @return string|null
     */
    public function getCanonicalUrl(): ?string;

    /**
     * @param string|null $canonicalUrl
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setCanonicalUrl(?string $canonicalUrl): PostInterface;

    /**
     * @return string|null
     */
    public function getBodyHtml(): ?string;

    /**
     * @param string|null $bodyHtml
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setBodyHtml(?string $bodyHtml): PostInterface;

    /**
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * @param int|null $storeId
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setStoreId(?int $storeId): PostInterface;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @param string|null $createdAt
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setCreatedAt(?string $createdAt): PostInterface;

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * @param string|null $updatedAt
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setUpdatedAt(?string $updatedAt): PostInterface;
}
