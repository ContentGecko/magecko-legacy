<?php
declare(strict_types=1);

namespace Magecko\Blog\Block\Frontend;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Cms\Model\Template\FilterProvider;
use Magecko\Blog\Model\BlogUrl;
use Magecko\Blog\Model\Post;

class PostView extends Template implements IdentityInterface
{
    protected $_template = 'Magecko_Blog::post.phtml';

    private $registry;
    private $filterProvider;
    private $blogUrl;

    public function __construct(
        Context $context,
        Registry $registry,
        FilterProvider $filterProvider,
        BlogUrl $blogUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->filterProvider = $filterProvider;
        $this->blogUrl = $blogUrl;
    }

    public function getPost(): Post
    {
        return $this->registry->registry('magecko_blog_post');
    }

    public function getIdentities(): array
    {
        $post = $this->getPost();
        if ($post && $post->getId()) {
            return $post->getIdentities();
        }

        return [Post::CACHE_TAG];
    }

    public function getBlogUrl(): string
    {
        return $this->blogUrl->getLandingUrl();
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

    public function formatPostDate($value): string
    {
        if (!$value) {
            return '';
        }

        return date('M j, Y', strtotime($value));
    }

    public function filterHtml(string $html): string
    {
        try {
            return $this->filterProvider->getPageFilter()->filter($html);
        } catch (\Exception $exception) {
            return $html;
        }
    }
}
