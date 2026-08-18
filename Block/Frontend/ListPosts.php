<?php
declare(strict_types=1);

namespace Magecko\Blog\Block\Frontend;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Cms\Model\Template\FilterProvider;
use Magecko\Blog\Model\BlogUrl;
use Magecko\Blog\Model\Config;
use Magecko\Blog\Model\Post as BlogPost;
use Magecko\Blog\Model\PostTranslation;
use Magecko\Blog\Model\ResourceModel\Post\Collection;
use Magecko\Blog\Model\ResourceModel\Post\CollectionFactory;

class ListPosts extends Template implements IdentityInterface
{
    private const PAGE_SIZE = 9;

    protected $_template = 'Magecko_Blog::list.phtml';

    private $collectionFactory;
    private $postTranslation;
    private $filterProvider;
    private $blogUrl;
    private $config;
    private $posts = null;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        PostTranslation $postTranslation,
        FilterProvider $filterProvider,
        BlogUrl $blogUrl,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->postTranslation = $postTranslation;
        $this->filterProvider = $filterProvider;
        $this->blogUrl = $blogUrl;
        $this->config = $config;
    }

    public function getHeading(): string
    {
        return $this->config->getHeading((int)$this->_storeManager->getStore()->getId());
    }

    public function getIntro(): string
    {
        return $this->config->getIntro((int)$this->_storeManager->getStore()->getId());
    }

    public function getPosts(): Collection
    {
        if ($this->posts) {
            return $this->posts;
        }

        $collection = $this->collectionFactory->create();
        $collection->addPublishedFilter();
        $collection->addPublicOrder();
        $collection->setPageSize(self::PAGE_SIZE);
        $collection->setCurPage($this->getCurrentPage());
        $this->postTranslation->applyToPosts($collection->getItems(), (int)$this->_storeManager->getStore()->getId());
        $this->posts = $collection;

        return $this->posts;
    }

    public function getIdentities(): array
    {
        $identities = [BlogPost::CACHE_TAG];
        foreach ($this->getPosts() as $post) {
            $identities = array_merge($identities, $post->getIdentities());
        }

        return array_values(array_unique($identities));
    }

    public function getPostUrl(string $slug): string
    {
        return $this->blogUrl->getPostUrl($slug);
    }

    public function getCurrentPage(): int
    {
        return max(1, (int)$this->getRequest()->getParam('p', 1));
    }

    public function getTotalPages(): int
    {
        return max(1, (int)ceil($this->getPosts()->getSize() / self::PAGE_SIZE));
    }

    public function getPagerUrl(int $page): string
    {
        $query = $page > 1 ? ['p' => $page] : [];
        return $this->blogUrl->getLandingUrl(null, $query);
    }

    public function getMediaUrl($path): string
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

    public function getExcerpt(string $html, int $limit = 180): string
    {
        $html = preg_replace('/<\/(h[1-6]|p|li|blockquote|ul|ol)>/i', ' ', $html) ?: $html;
        $text = preg_replace('/\s+/', ' ', trim(strip_tags($html))) ?: '';
        if (strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(substr($text, 0, $limit), " \t\n\r\0\x0B.,") . '...';
    }

    public function filterHtml(string $html): string
    {
        try {
            return $this->filterProvider->getPageFilter()->filter($html);
        } catch (\Exception $exception) {
            return $html;
        }
    }

    public function formatPostDate($value): string
    {
        if (!$value) {
            return '';
        }

        return date('M j, Y', strtotime($value));
    }
}
