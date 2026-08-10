<?php
declare(strict_types=1);

namespace Magecko\Blog\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magecko\Blog\Api\Data\PostInterface;
use Magecko\Blog\Api\Data\PostSearchResultsInterface;

interface PostRepositoryInterface
{
    /**
     * Create a blog post.
     *
     * @param \Magecko\Blog\Api\Data\PostInterface $post
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function save(PostInterface $post): PostInterface;

    /**
     * Update a blog post by ID.
     *
     * @param int $postId
     * @param \Magecko\Blog\Api\Data\PostInterface $post
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function saveById(int $postId, PostInterface $post): PostInterface;

    /**
     * Save a store-view translation for a blog post.
     *
     * @param int $postId
     * @param int $storeId
     * @param \Magecko\Blog\Api\Data\PostInterface $post
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function saveTranslation(int $postId, int $storeId, PostInterface $post): PostInterface;

    /**
     * Get a blog post by ID.
     *
     * @param int $postId
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function getById(int $postId): PostInterface;

    /**
     * Get a blog post by ID with store-view translated fields applied.
     *
     * @param int $postId
     * @param int $storeId
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function getByIdForStore(int $postId, int $storeId): PostInterface;

    /**
     * Get a blog post by slug.
     *
     * @param string $slug
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function getBySlug(string $slug): PostInterface;

    /**
     * Get a blog post by canonical or translated slug for a store view.
     *
     * @param string $slug
     * @param int $storeId
     * @return \Magecko\Blog\Api\Data\PostInterface
     */
    public function getBySlugForStore(string $slug, int $storeId): PostInterface;

    /**
     * Search blog posts.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magecko\Blog\Api\Data\PostSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): PostSearchResultsInterface;

    /**
     * Delete a blog post by ID.
     *
     * @param int $postId
     * @return bool
     */
    public function deleteById(int $postId): bool;

    /**
     * Delete a store-view translation for a blog post.
     *
     * @param int $postId
     * @param int $storeId
     * @return bool
     */
    public function deleteTranslation(int $postId, int $storeId): bool;
}
