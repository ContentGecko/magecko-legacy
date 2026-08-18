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
    public function getPostId();

    /**
     * @param int|null $postId
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setPostId($postId): PostInterface;

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @param string|null $title
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setTitle($title): PostInterface;

    /**
     * @return string|null
     */
    public function getSlug();

    /**
     * @param string|null $slug
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setSlug($slug): PostInterface;

    /**
     * @return string|null
     */
    public function getStatus();

    /**
     * @param string|null $status
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setStatus($status): PostInterface;

    /**
     * @return string|null
     */
    public function getTopic();

    /**
     * @param string|null $topic
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setTopic($topic): PostInterface;

    /**
     * @return string|null
     */
    public function getAuthor();

    /**
     * @param string|null $author
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setAuthor($author): PostInterface;

    /**
     * @return string|null
     */
    public function getPublishDate();

    /**
     * @param string|null $publishDate
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setPublishDate($publishDate): PostInterface;

    /**
     * @return string|null
     */
    public function getModifiedDate();

    /**
     * @param string|null $modifiedDate
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setModifiedDate($modifiedDate): PostInterface;

    /**
     * @return string|null
     */
    public function getFeaturedImage();

    /**
     * @param string|null $featuredImage
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setFeaturedImage($featuredImage): PostInterface;

    /**
     * @return string|null
     */
    public function getFeaturedImageAlt();

    /**
     * @param string|null $featuredImageAlt
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setFeaturedImageAlt($featuredImageAlt): PostInterface;

    /**
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * @param string|null $metaTitle
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setMetaTitle($metaTitle): PostInterface;

    /**
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * @param string|null $metaDescription
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setMetaDescription($metaDescription): PostInterface;

    /**
     * @return string|null
     */
    public function getCanonicalUrl();

    /**
     * @param string|null $canonicalUrl
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setCanonicalUrl($canonicalUrl): PostInterface;

    /**
     * @return string|null
     */
    public function getBodyHtml();

    /**
     * @param string|null $bodyHtml
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setBodyHtml($bodyHtml): PostInterface;

    /**
     * @return int|null
     */
    public function getStoreId();

    /**
     * @param int|null $storeId
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setStoreId($storeId): PostInterface;

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string|null $createdAt
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setCreatedAt($createdAt): PostInterface;

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param string|null $updatedAt
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function setUpdatedAt($updatedAt): PostInterface;
}
