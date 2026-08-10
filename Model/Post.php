<?php
declare(strict_types=1);

namespace Magecko\Blog\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magecko\Blog\Api\Data\PostInterface;

class Post extends AbstractModel implements IdentityInterface, PostInterface
{
    public const CACHE_TAG = 'magecko_blog_post';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
    ];

    protected function _construct(): void
    {
        $this->_init(ResourceModel\Post::class);
    }

    public function getIdFieldName(): string
    {
        return 'post_id';
    }

    public function getIdentities(): array
    {
        if ($this->getId()) {
            return [self::CACHE_TAG, self::CACHE_TAG . '_' . $this->getId()];
        }

        return [self::CACHE_TAG];
    }

    public function getPostId(): ?int
    {
        return $this->getId() ? (int)$this->getId() : null;
    }

    public function setPostId(?int $postId): PostInterface
    {
        return $this->setData(self::POST_ID, $postId);
    }

    public function getTitle(): ?string
    {
        return $this->getData(self::TITLE) !== null ? (string)$this->getData(self::TITLE) : null;
    }

    public function setTitle(?string $title): PostInterface
    {
        return $this->setData(self::TITLE, $title);
    }

    public function getSlug(): ?string
    {
        return $this->getData(self::SLUG) !== null ? (string)$this->getData(self::SLUG) : null;
    }

    public function setSlug(?string $slug): PostInterface
    {
        return $this->setData(self::SLUG, $slug);
    }

    public function getStatus(): ?string
    {
        return $this->getData(self::STATUS) !== null ? (string)$this->getData(self::STATUS) : null;
    }

    public function setStatus(?string $status): PostInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getTopic(): ?string
    {
        return $this->getData(self::TOPIC) !== null ? (string)$this->getData(self::TOPIC) : null;
    }

    public function setTopic(?string $topic): PostInterface
    {
        return $this->setData(self::TOPIC, $topic);
    }

    public function getAuthor(): ?string
    {
        return $this->getData(self::AUTHOR) !== null ? (string)$this->getData(self::AUTHOR) : null;
    }

    public function setAuthor(?string $author): PostInterface
    {
        return $this->setData(self::AUTHOR, $author);
    }

    public function getPublishDate(): ?string
    {
        return $this->getData(self::PUBLISH_DATE) !== null ? (string)$this->getData(self::PUBLISH_DATE) : null;
    }

    public function setPublishDate(?string $publishDate): PostInterface
    {
        return $this->setData(self::PUBLISH_DATE, $publishDate);
    }

    public function getModifiedDate(): ?string
    {
        return $this->getData(self::MODIFIED_DATE) !== null ? (string)$this->getData(self::MODIFIED_DATE) : null;
    }

    public function setModifiedDate(?string $modifiedDate): PostInterface
    {
        return $this->setData(self::MODIFIED_DATE, $modifiedDate);
    }

    public function getFeaturedImage(): ?string
    {
        return $this->getData(self::FEATURED_IMAGE) !== null ? (string)$this->getData(self::FEATURED_IMAGE) : null;
    }

    public function setFeaturedImage(?string $featuredImage): PostInterface
    {
        return $this->setData(self::FEATURED_IMAGE, $featuredImage);
    }

    public function getFeaturedImageAlt(): ?string
    {
        return $this->getData(self::FEATURED_IMAGE_ALT) !== null ? (string)$this->getData(self::FEATURED_IMAGE_ALT) : null;
    }

    public function setFeaturedImageAlt(?string $featuredImageAlt): PostInterface
    {
        return $this->setData(self::FEATURED_IMAGE_ALT, $featuredImageAlt);
    }

    public function getMetaTitle(): ?string
    {
        return $this->getData(self::META_TITLE) !== null ? (string)$this->getData(self::META_TITLE) : null;
    }

    public function setMetaTitle(?string $metaTitle): PostInterface
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    public function getMetaDescription(): ?string
    {
        return $this->getData(self::META_DESCRIPTION) !== null ? (string)$this->getData(self::META_DESCRIPTION) : null;
    }

    public function setMetaDescription(?string $metaDescription): PostInterface
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->getData(self::CANONICAL_URL) !== null ? (string)$this->getData(self::CANONICAL_URL) : null;
    }

    public function setCanonicalUrl(?string $canonicalUrl): PostInterface
    {
        return $this->setData(self::CANONICAL_URL, $canonicalUrl);
    }

    public function getBodyHtml(): ?string
    {
        return $this->getData(self::BODY_HTML) !== null ? (string)$this->getData(self::BODY_HTML) : null;
    }

    public function setBodyHtml(?string $bodyHtml): PostInterface
    {
        return $this->setData(self::BODY_HTML, $bodyHtml);
    }

    public function getStoreId(): ?int
    {
        return $this->getData(self::STORE_ID) !== null ? (int)$this->getData(self::STORE_ID) : null;
    }

    public function setStoreId(?int $storeId): PostInterface
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT) !== null ? (string)$this->getData(self::CREATED_AT) : null;
    }

    public function setCreatedAt(?string $createdAt): PostInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT) !== null ? (string)$this->getData(self::UPDATED_AT) : null;
    }

    public function setUpdatedAt(?string $updatedAt): PostInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
