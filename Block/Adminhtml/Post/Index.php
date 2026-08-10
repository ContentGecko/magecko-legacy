<?php
declare(strict_types=1);

namespace Magecko\Blog\Block\Adminhtml\Post;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use Magecko\Blog\Model\Post;
use Magecko\Blog\Model\BlogUrl;
use Magecko\Blog\Model\Config;
use Magecko\Blog\Model\ResourceModel\Post\Collection;
use Magecko\Blog\Model\ResourceModel\Post\CollectionFactory;

class Index extends Template
{
    private const DEFAULT_PAGE_SIZE = 20;
    private const PAGE_SIZE_OPTIONS = [20, 50, 100];

    protected $_template = 'Magecko_Blog::post/index.phtml';

    private $collectionFactory;
    private $blogFormKey;
    private $posts = null;
    private $blogUrl;
    private $config;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        FormKey $formKey,
        BlogUrl $blogUrl,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->blogFormKey = $formKey;
        $this->blogUrl = $blogUrl;
        $this->config = $config;
    }

    public function getPosts(): Collection
    {
        if ($this->posts) {
            return $this->posts;
        }

        $collection = $this->collectionFactory->create();
        $collection->addTitleFilter($this->getTitleFilter());
        $collection->addStatusFilter($this->getStatusFilter());
        $collection->addTopicFilter($this->getTopicFilter());
        $collection->addAuthorFilter($this->getAuthorFilter());
        $collection->setOrder('modified_date', Collection::SORT_ORDER_DESC);
        $collection->setOrder('post_id', Collection::SORT_ORDER_DESC);
        $collection->setPageSize($this->getPageSize());
        $collection->setCurPage($this->getCurrentPage());
        $this->posts = $collection;

        return $this->posts;
    }

    public function getTitleFilter(): string
    {
        return trim((string)$this->getRequest()->getParam('title', ''));
    }

    public function getStatusFilter(): string
    {
        $status = trim((string)$this->getRequest()->getParam('status', ''));
        return in_array($status, Post::STATUSES, true) ? $status : '';
    }

    public function getTopicFilter(): string
    {
        return trim((string)$this->getRequest()->getParam('topic', ''));
    }

    public function getAuthorFilter(): string
    {
        return trim((string)$this->getRequest()->getParam('author', ''));
    }

    public function getCurrentPage(): int
    {
        return max(1, (int)$this->getRequest()->getParam('p', 1));
    }

    public function getPageSize(): int
    {
        $pageSize = (int)$this->getRequest()->getParam('limit', self::DEFAULT_PAGE_SIZE);
        return in_array($pageSize, self::PAGE_SIZE_OPTIONS, true) ? $pageSize : self::DEFAULT_PAGE_SIZE;
    }

    public function getPageSizeOptions(): array
    {
        return self::PAGE_SIZE_OPTIONS;
    }

    public function getTotalPages(): int
    {
        return max(1, (int)ceil($this->getPosts()->getSize() / $this->getPageSize()));
    }

    public function getPagerUrl(int $page): string
    {
        return $this->getUrl('magecko_blog/post/index', ['_query' => $this->getQueryParams(['p' => max(1, $page)])]);
    }

    public function getFilterUrl(): string
    {
        return $this->getUrl('magecko_blog/post/index');
    }

    public function getClearFilterUrl(): string
    {
        return $this->getUrl('magecko_blog/post/index');
    }

    public function getNewUrl(): string
    {
        return $this->getUrl('magecko_blog/post/new');
    }

    public function getEditUrl(int $postId): string
    {
        return $this->getUrl('magecko_blog/post/edit', ['post_id' => $postId]);
    }

    public function getDeleteUrl(int $postId): string
    {
        return $this->getUrl('magecko_blog/post/delete', ['post_id' => $postId]);
    }

    public function getFormKey(): string
    {
        return $this->blogFormKey->getFormKey();
    }

    public function getBlogUrl(string $slug): string
    {
        return $this->blogUrl->getPostUrl($slug);
    }

    public function isStorefrontEnabled(): bool
    {
        return $this->config->isStorefrontEnabled(
            (int)$this->_storeManager->getDefaultStoreView()->getId()
        );
    }

    public function getStatusLabel(?string $status): string
    {
        return (string)ucfirst(str_replace('_', ' ', (string)$status));
    }

    public function isPublished(?string $status): bool
    {
        return $status === Post::STATUS_PUBLISHED;
    }

    private function getQueryParams(array $overrides = []): array
    {
        $params = [
            'title' => $this->getTitleFilter(),
            'status' => $this->getStatusFilter(),
            'topic' => $this->getTopicFilter(),
            'author' => $this->getAuthorFilter(),
            'limit' => $this->getPageSize(),
            'p' => $this->getCurrentPage(),
        ];

        $params = array_merge($params, $overrides);
        return array_filter($params, static function ($value): bool {
            return $value !== '' && $value !== null;
        });
    }
}
