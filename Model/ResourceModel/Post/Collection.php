<?php
declare(strict_types=1);

namespace Magecko\Blog\Model\ResourceModel\Post;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magecko\Blog\Model\Post;
use Magecko\Blog\Model\ResourceModel\Post as PostResource;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(Post::class, PostResource::class);
    }

    public function addPublicOrder(): Collection
    {
        $this->setOrder('publish_date', self::SORT_ORDER_DESC);
        $this->setOrder('post_id', self::SORT_ORDER_DESC);
        return $this;
    }

    public function addPublishedFilter(): Collection
    {
        $this->addFieldToFilter('status', Post::STATUS_PUBLISHED);
        return $this;
    }

    public function addStatusFilter(string $status): Collection
    {
        $status = trim($status);
        if (in_array($status, Post::STATUSES, true)) {
            $this->addFieldToFilter('status', $status);
        }

        return $this;
    }

    public function addTitleFilter(string $query): Collection
    {
        $query = trim($query);
        if ($query !== '') {
            $this->addFieldToFilter('title', ['like' => '%' . $query . '%']);
        }

        return $this;
    }

    public function addTopicFilter(string $query): Collection
    {
        $query = trim($query);
        if ($query !== '') {
            $this->addFieldToFilter('topic', ['like' => '%' . $query . '%']);
        }

        return $this;
    }

    public function addAuthorFilter(string $query): Collection
    {
        $query = trim($query);
        if ($query !== '') {
            $this->addFieldToFilter('author', ['like' => '%' . $query . '%']);
        }

        return $this;
    }
}
