<?php
declare(strict_types=1);

namespace Magecko\Blog\Model;

use Magento\Framework\App\ResourceConnection;
use Magecko\Blog\Api\Data\PostInterface;

class PostTranslation
{
    private const TABLE = 'magecko_blog_post_store';

    private const TRANSLATABLE_FIELDS = [
        PostInterface::TITLE,
        PostInterface::SLUG,
        PostInterface::TOPIC,
        PostInterface::AUTHOR,
        PostInterface::FEATURED_IMAGE_ALT,
        PostInterface::META_TITLE,
        PostInterface::META_DESCRIPTION,
        PostInterface::CANONICAL_URL,
        PostInterface::BODY_HTML,
    ];

    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public function get(int $postId, int $storeId): array
    {
        if ($postId <= 0 || $storeId <= 0) {
            return [];
        }

        $connection = $this->resource->getConnection();
        $row = $connection->fetchRow(
            $connection->select()
                ->from($this->resource->getTableName(self::TABLE))
                ->where('post_id = ?', $postId)
                ->where('store_id = ?', $storeId)
                ->limit(1)
        );

        return is_array($row) ? $row : [];
    }

    public function getByStoreSlug(string $slug, int $storeId): array
    {
        $slug = trim($slug);
        if ($slug === '' || $storeId <= 0) {
            return [];
        }

        $connection = $this->resource->getConnection();
        $row = $connection->fetchRow(
            $connection->select()
                ->from($this->resource->getTableName(self::TABLE))
                ->where('slug = ?', $slug)
                ->where('store_id = ?', $storeId)
                ->limit(1)
        );

        return is_array($row) ? $row : [];
    }

    public function getByPostIds(array $postIds, int $storeId): array
    {
        $postIds = array_values(array_filter(array_map('intval', $postIds)));
        if (!$postIds || $storeId <= 0) {
            return [];
        }

        $connection = $this->resource->getConnection();
        $rows = $connection->fetchAll(
            $connection->select()
                ->from($this->resource->getTableName(self::TABLE))
                ->where('post_id IN (?)', $postIds)
                ->where('store_id = ?', $storeId)
        );

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int)$row['post_id']] = $row;
        }

        return $indexed;
    }

    public function getAllForPost(int $postId): array
    {
        if ($postId <= 0) {
            return [];
        }

        $connection = $this->resource->getConnection();
        $rows = $connection->fetchAll(
            $connection->select()
                ->from($this->resource->getTableName(self::TABLE))
                ->where('post_id = ?', $postId)
        );

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int)$row['store_id']] = $row;
        }

        return $indexed;
    }

    public function applyToPost(Post $post, int $storeId): Post
    {
        $translation = $this->get((int)$post->getId(), $storeId);
        if (!$translation) {
            $post->setStoreId($storeId > 0 ? $storeId : null);
            return $post;
        }

        foreach (self::TRANSLATABLE_FIELDS as $field) {
            if (array_key_exists($field, $translation) && trim((string)$translation[$field]) !== '') {
                $post->setData($field, $translation[$field]);
            }
        }

        $post->setStoreId($storeId);
        return $post;
    }

    public function applyToPosts(iterable $posts, int $storeId): void
    {
        if ($storeId <= 0) {
            return;
        }

        $postIds = [];
        foreach ($posts as $post) {
            if ($post instanceof Post && $post->getId()) {
                $postIds[] = (int)$post->getId();
            }
        }

        $translations = $this->getByPostIds($postIds, $storeId);
        foreach ($posts as $post) {
            if (!$post instanceof Post || !$post->getId()) {
                continue;
            }

            $translation = $translations[(int)$post->getId()] ?? [];
            foreach (self::TRANSLATABLE_FIELDS as $field) {
                if (array_key_exists($field, $translation) && trim((string)$translation[$field]) !== '') {
                    $post->setData($field, $translation[$field]);
                }
            }
            $post->setStoreId($storeId);
        }
    }

    public function save(int $postId, int $storeId, array $data): void
    {
        if ($postId <= 0 || $storeId <= 0) {
            throw new \InvalidArgumentException('A valid post ID and store ID are required.');
        }

        $row = [
            PostInterface::POST_ID => $postId,
            PostInterface::STORE_ID => $storeId,
        ];

        foreach (self::TRANSLATABLE_FIELDS as $field) {
            $value = isset($data[$field]) ? trim((string)$data[$field]) : null;
            $row[$field] = $value !== '' ? $value : null;
        }

        if (!$this->hasTranslatedContent($row)) {
            $this->delete($postId, $storeId);
            return;
        }

        $this->resource->getConnection()->insertOnDuplicate(
            $this->resource->getTableName(self::TABLE),
            $row,
            self::TRANSLATABLE_FIELDS
        );
    }

    public function delete(int $postId, int $storeId): void
    {
        if ($postId <= 0 || $storeId <= 0) {
            return;
        }

        $this->resource->getConnection()->delete(
            $this->resource->getTableName(self::TABLE),
            ['post_id = ?' => $postId, 'store_id = ?' => $storeId]
        );
    }

    private function hasTranslatedContent(array $row): bool
    {
        foreach (self::TRANSLATABLE_FIELDS as $field) {
            if (trim((string)($row[$field] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }
}
