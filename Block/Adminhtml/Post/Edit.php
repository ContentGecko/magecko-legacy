<?php
declare(strict_types=1);

namespace Magecko\Blog\Block\Adminhtml\Post;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magecko\Blog\Model\Post;
use Magecko\Blog\Model\PostTranslation;
use Magecko\Blog\Model\BlogUrl;
use Magecko\Blog\Model\Config;

class Edit extends Template
{
    protected $_template = 'Magecko_Blog::post/edit.phtml';

    private $registry;
    private $blogFormKey;
    private $dataPersistor;
    private $storeManager;
    private $postTranslation;
    private $resource;
    private $blogUrl;
    private $config;
    private $wysiwygConfig;

    public function __construct(
        Context $context,
        Registry $registry,
        FormKey $formKey,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        PostTranslation $postTranslation,
        ResourceConnection $resource,
        BlogUrl $blogUrl,
        Config $config,
        WysiwygConfig $wysiwygConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->blogFormKey = $formKey;
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        $this->postTranslation = $postTranslation;
        $this->resource = $resource;
        $this->blogUrl = $blogUrl;
        $this->config = $config;
        $this->wysiwygConfig = $wysiwygConfig;
    }

    public function getPost(): Post
    {
        return $this->registry->registry('magecko_blog_post');
    }

    public function getFormData(): array
    {
        $persisted = $this->dataPersistor->get('magecko_blog_post');
        if (is_array($persisted) && $persisted) {
            return $persisted;
        }

        return $this->getPost()->getData();
    }

    public function getSaveUrl(): string
    {
        return $this->getUrl('magecko_blog/post/save');
    }

    public function getBackUrl(): string
    {
        return $this->getUrl('magecko_blog/post/index');
    }

    public function getMediaGalleryUrl(): string
    {
        return $this->getUrl('media_gallery/media/index');
    }

    public function getBlogUrl(): string
    {
        $slug = (string)($this->getFormData()['slug'] ?? '');
        return $this->blogUrl->getPostUrl($slug);
    }

    public function isStorefrontEnabled(): bool
    {
        return $this->config->isStorefrontEnabled($this->getDefaultStoreId());
    }

    public function getDeleteUrl(): string
    {
        return $this->getUrl('magecko_blog/post/delete', ['post_id' => (int)$this->getPost()->getId()]);
    }

    public function getFormKey(): string
    {
        return $this->blogFormKey->getFormKey();
    }

    public function formatForDatetimeInput(?string $value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return (new \DateTime($value))->format('Y-m-d\TH:i');
        } catch (\Exception $exception) {
            return '';
        }
    }

    public function getMediaUrl(?string $path): string
    {
        $path = ltrim((string)$path, '/');
        if ($path === '') {
            return '';
        }

        if (strpos($path, 'media/') === 0) {
            $path = substr($path, strlen('media/'));
        }

        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path;
    }

    /**
     * @return StoreInterface[]
     */
    public function getStoreViews(): array
    {
        $defaultStoreId = $this->getDefaultStoreId();
        return array_values(array_filter(
            $this->storeManager->getStores(false),
            static function (StoreInterface $store) use ($defaultStoreId): bool {
                return (int)$store->getId() !== $defaultStoreId;
            }
        ));
    }

    public function getTopicOptions(): array
    {
        return $this->getDistinctOptionValues('topic');
    }

    public function getAuthorOptions(): array
    {
        return $this->getDistinctOptionValues('author');
    }

    public function getWysiwygConfigJson(): string
    {
        $config = $this->wysiwygConfig->getConfig([
            'height' => '420px',
            'width' => '100%',
        ]);

        return $config->toJson();
    }

    public function getTranslationData(int $storeId): array
    {
        $persisted = $this->dataPersistor->get('magecko_blog_post');
        if (is_array($persisted) && isset($persisted['translations'][$storeId]) && is_array($persisted['translations'][$storeId])) {
            return $persisted['translations'][$storeId];
        }

        $post = $this->getPost();
        if (!$post->getId()) {
            return [];
        }

        return $this->postTranslation->get((int)$post->getId(), $storeId);
    }

    private function getDistinctOptionValues(string $field): array
    {
        if (!in_array($field, ['topic', 'author'], true)) {
            return [];
        }

        $connection = $this->resource->getConnection();
        $mainSelect = $connection->select()
            ->from($this->resource->getTableName('magecko_blog_post'), [$field])
            ->where($field . ' IS NOT NULL')
            ->where($field . ' != ?', '');

        $translationSelect = $connection->select()
            ->from($this->resource->getTableName('magecko_blog_post_store'), [$field])
            ->where($field . ' IS NOT NULL')
            ->where($field . ' != ?', '');

        $values = $connection->fetchCol($connection->select()->union([$mainSelect, $translationSelect]));
        $values = array_values(array_unique(array_filter(array_map(
            static function ($value): string {
                return trim((string)$value);
            },
            $values
        ))));
        natcasesort($values);

        return array_values($values);
    }

    private function getDefaultStoreId(): int
    {
        return (int)$this->storeManager->getDefaultStoreView()->getId();
    }
}
