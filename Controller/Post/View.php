<?php
declare(strict_types=1);

namespace Magecko\Blog\Controller\Post;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magecko\Blog\Api\PostRepositoryInterface;
use Magecko\Blog\Model\BlogUrl;
use Magecko\Blog\Model\Config;
use Magecko\Blog\Model\Post as BlogPost;
use Magecko\Blog\Model\PostTranslation;

class View extends Action
{
    private $resultPageFactory;
    private $forwardFactory;
    private $postRepository;
    private $storeManager;
    private $scopeConfig;
    private $postTranslation;
    private $registry;
    private $config;
    private $blogUrl;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $forwardFactory,
        PostRepositoryInterface $postRepository,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        PostTranslation $postTranslation,
        Registry $registry,
        Config $config,
        BlogUrl $blogUrl
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->postRepository = $postRepository;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->postTranslation = $postTranslation;
        $this->registry = $registry;
        $this->config = $config;
        $this->blogUrl = $blogUrl;
    }

    public function execute()
    {
        if (!$this->config->isStorefrontEnabled() || !$this->getRequest()->getParam('_magecko_routed')) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        $slug = (string)$this->getRequest()->getParam('slug', '');
        $storeId = (int)$this->storeManager->getStore()->getId();

        try {
            $post = $this->postRepository->getBySlugForStore($slug, $storeId);
            $basePost = $this->postRepository->getById((int)$post->getPostId());
        } catch (NoSuchEntityException $exception) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        if ((string)$basePost->getData('status') !== BlogPost::STATUS_PUBLISHED) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        $this->registry->register('magecko_blog_post', $post);
        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->set($this->getMetaTitle($post));
        if (trim((string)$post->getData('meta_description')) !== '') {
            $page->getConfig()->setDescription((string)$post->getData('meta_description'));
        }
        $this->addCanonicalAndHreflang($page, $post, $basePost instanceof BlogPost ? $basePost : $post);
        return $page;
    }

    private function getMetaTitle(BlogPost $post): string
    {
        $metaTitle = trim((string)$post->getData('meta_title'));
        return $metaTitle !== '' ? $metaTitle : (string)$post->getData('title');
    }

    private function addCanonicalAndHreflang($page, BlogPost $post, BlogPost $basePost): void
    {
        $currentUrl = $this->getStorePostUrl((int)$this->storeManager->getStore()->getId(), (string)$post->getData('slug'));
        $canonicalUrl = trim((string)$post->getData('canonical_url')) ?: $currentUrl;
        $page->getConfig()->addRemotePageAsset(
            $canonicalUrl,
            'link',
            ['attributes' => ['rel' => 'canonical']]
        );

        $translations = $this->postTranslation->getAllForPost((int)$basePost->getId());
        foreach ($this->storeManager->getStores(false) as $store) {
            if (!$store->getIsActive()) {
                continue;
            }

            $storeId = (int)$store->getId();
            $translation = $translations[$storeId] ?? [];
            $slug = trim((string)($translation['slug'] ?? '')) ?: (string)$basePost->getData('slug');
            if ($slug === '') {
                continue;
            }

            $page->getConfig()->addRemotePageAsset(
                $this->getStorePostUrl($storeId, $slug),
                'link',
                ['attributes' => ['rel' => 'alternate', 'hreflang' => $this->getStoreHreflang($storeId)]],
                'magecko-blog-hreflang-' . $storeId
            );
        }

        $defaultStore = $this->storeManager->getDefaultStoreView();
        $defaultSlug = (string)$basePost->getData('slug');
        if ($defaultSlug !== '') {
            $page->getConfig()->addRemotePageAsset(
                $this->getStorePostUrl((int)$defaultStore->getId(), $defaultSlug),
                'link',
                ['attributes' => ['rel' => 'alternate', 'hreflang' => 'x-default']],
                'magecko-blog-hreflang-x-default'
            );
        }
    }

    private function getStorePostUrl(int $storeId, string $slug): string
    {
        return $this->blogUrl->getPostUrl($slug, $storeId);
    }

    private function getStoreHreflang(int $storeId): string
    {
        $locale = (string)$this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORE, $storeId);
        $locale = trim($locale) ?: (string)$this->storeManager->getStore($storeId)->getCode();
        return strtolower(str_replace('_', '-', $locale));
    }
}
