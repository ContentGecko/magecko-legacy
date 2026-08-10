<?php
declare(strict_types=1);

namespace Magecko\Blog\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magecko\Blog\Api\Data\PostInterface;
use Magecko\Blog\Api\Data\PostSearchResultsInterface;
use Magecko\Blog\Api\Data\PostSearchResultsInterfaceFactory;
use Magecko\Blog\Api\PostRepositoryInterface;
use Magecko\Blog\Model\ResourceModel\Post as PostResource;
use Magecko\Blog\Model\ResourceModel\Post\CollectionFactory;

class PostRepository implements PostRepositoryInterface
{
    private $postFactory;
    private $postResource;
    private $collectionFactory;
    private $searchResultsFactory;
    private $collectionProcessor;
    private $cache;
    private $dateTime;
    private $postTranslation;

    public function __construct(
        PostFactory $postFactory,
        PostResource $postResource,
        CollectionFactory $collectionFactory,
        PostSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        CacheInterface $cache,
        DateTime $dateTime,
        PostTranslation $postTranslation
    ) {
        $this->postFactory = $postFactory;
        $this->postResource = $postResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->cache = $cache;
        $this->dateTime = $dateTime;
        $this->postTranslation = $postTranslation;
    }

    public function save(PostInterface $post): PostInterface
    {
        $model = $post instanceof Post ? $post : $this->postFactory->create()->addData($this->extractData($post));
        $this->prepareForSave($model);

        try {
            $this->postResource->save($model);
            $this->cache->clean($model->getIdentities());
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()), $exception);
        }

        return $model;
    }

    public function saveById(int $postId, PostInterface $post): PostInterface
    {
        $existing = $this->getById($postId);
        $existing->addData($this->extractData($post));
        $existing->setPostId($postId);
        return $this->save($existing);
    }

    public function saveTranslation(int $postId, int $storeId, PostInterface $post): PostInterface
    {
        $basePost = $this->getById($postId);
        $translation = $this->postFactory->create();
        $translation->addData(array_merge($basePost->getData(), $this->extractData($post)));
        $translation->setPostId($postId);
        $translation->setStoreId($storeId);
        $this->prepareForSave($translation);

        try {
            $this->postTranslation->save($postId, $storeId, $this->extractData($translation));
            $this->cache->clean($basePost->getIdentities());
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()), $exception);
        }

        return $this->getByIdForStore($postId, $storeId);
    }

    public function getById(int $postId): PostInterface
    {
        $post = $this->postFactory->create();
        $this->postResource->load($post, $postId);
        if (!$post->getId()) {
            throw NoSuchEntityException::singleField(PostInterface::POST_ID, $postId);
        }

        return $post;
    }

    public function getByIdForStore(int $postId, int $storeId): PostInterface
    {
        $post = $this->getById($postId);
        if ($post instanceof Post) {
            return $this->postTranslation->applyToPost($post, $storeId);
        }

        return $post;
    }

    public function getBySlug(string $slug): PostInterface
    {
        $post = $this->postFactory->create();
        $this->postResource->load($post, $slug, PostInterface::SLUG);
        if (!$post->getId()) {
            throw NoSuchEntityException::singleField(PostInterface::SLUG, $slug);
        }

        return $post;
    }

    public function getBySlugForStore(string $slug, int $storeId): PostInterface
    {
        if ($storeId > 0) {
            $translation = $this->postTranslation->getByStoreSlug($slug, $storeId);
            if ($translation) {
                return $this->getByIdForStore((int)$translation[PostInterface::POST_ID], $storeId);
            }
        }

        $post = $this->getBySlug($slug);
        if ($post instanceof Post) {
            return $this->postTranslation->applyToPost($post, $storeId);
        }

        return $post;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): PostSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    public function deleteById(int $postId): bool
    {
        $post = $this->getById($postId);
        $identities = $post->getIdentities();

        try {
            $this->postResource->delete($post);
            $this->cache->clean($identities);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()), $exception);
        }

        return true;
    }

    public function deleteTranslation(int $postId, int $storeId): bool
    {
        $post = $this->getById($postId);
        try {
            $this->postTranslation->delete($postId, $storeId);
            $this->cache->clean($post instanceof Post ? $post->getIdentities() : [Post::CACHE_TAG]);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()), $exception);
        }

        return true;
    }

    private function prepareForSave(Post $post): void
    {
        $now = $this->dateTime->gmtDate('Y-m-d H:i:s');
        $post->setTitle(trim((string)$post->getTitle()));
        $post->setSlug($this->normalizeSlug((string)($post->getSlug() ?: $post->getTitle())));
        $post->setStatus($this->normalizeStatus((string)$post->getStatus()));
        $post->setTopic(trim((string)$post->getTopic()));
        $post->setAuthor(trim((string)$post->getAuthor()));
        $post->setFeaturedImage($this->normalizeStoredMediaPath((string)$post->getFeaturedImage()));
        $post->setFeaturedImageAlt(trim((string)$post->getFeaturedImageAlt()));
        $post->setMetaTitle(trim((string)$post->getMetaTitle()));
        $post->setMetaDescription(trim((string)$post->getMetaDescription()));
        $post->setCanonicalUrl($this->normalizeCanonicalUrl((string)$post->getCanonicalUrl()));
        $post->setPublishDate($this->normalizeDate((string)$post->getPublishDate()) ?: $now);
        $post->setModifiedDate($this->normalizeDate((string)$post->getModifiedDate()) ?: $now);

        $post->setBodyHtml(trim((string)$post->getBodyHtml()));

        $this->validate($post);
    }

    private function extractData(PostInterface $post): array
    {
        return [
            PostInterface::POST_ID => $post->getPostId(),
            PostInterface::TITLE => $post->getTitle(),
            PostInterface::SLUG => $post->getSlug(),
            PostInterface::STATUS => $post->getStatus(),
            PostInterface::TOPIC => $post->getTopic(),
            PostInterface::AUTHOR => $post->getAuthor(),
            PostInterface::PUBLISH_DATE => $post->getPublishDate(),
            PostInterface::MODIFIED_DATE => $post->getModifiedDate(),
            PostInterface::FEATURED_IMAGE => $post->getFeaturedImage(),
            PostInterface::FEATURED_IMAGE_ALT => $post->getFeaturedImageAlt(),
            PostInterface::META_TITLE => $post->getMetaTitle(),
            PostInterface::META_DESCRIPTION => $post->getMetaDescription(),
            PostInterface::CANONICAL_URL => $post->getCanonicalUrl(),
            PostInterface::BODY_HTML => $post->getBodyHtml(),
            PostInterface::STORE_ID => $post->getStoreId(),
        ];
    }

    private function validate(Post $post): void
    {
        if ((string)$post->getTitle() === '') {
            throw new \InvalidArgumentException('Title is required.');
        }

        if ((string)$post->getSlug() === '') {
            throw new \InvalidArgumentException('Slug is required.');
        }

        if (!in_array((string)$post->getStatus(), Post::STATUSES, true)) {
            throw new \InvalidArgumentException('Status must be draft or published.');
        }

        if ((string)$post->getBodyHtml() === '') {
            throw new \InvalidArgumentException('Article body is required.');
        }
    }

    private function normalizeStatus(string $value): string
    {
        $value = strtolower(trim($value));
        return in_array($value, Post::STATUSES, true) ? $value : Post::STATUS_DRAFT;
    }

    private function normalizeSlug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?: '';
        return trim($value, '-');
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return (new \DateTime($value))->format('Y-m-d H:i:s');
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function normalizeCanonicalUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return preg_match('#^https?://#i', $value) ? $value : '';
    }

    private function normalizeStoredMediaPath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = ltrim($path, '/');
        if (strpos($path, 'media/') === 0) {
            $path = substr($path, strlen('media/'));
        }

        return preg_match('#^magecko/blog/[A-Za-z0-9._/-]+$#', $path) ? $path : '';
    }
}
