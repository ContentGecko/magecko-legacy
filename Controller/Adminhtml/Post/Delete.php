<?php
declare(strict_types=1);

namespace Magecko\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magecko\Blog\Controller\Adminhtml\Post;
use Magecko\Blog\Model\PostFactory;

class Delete extends Post
{
    private $postFactory;
    private $cache;

    public function __construct(Context $context, PostFactory $postFactory, CacheInterface $cache)
    {
        parent::__construct($context);
        $this->postFactory = $postFactory;
        $this->cache = $cache;
    }

    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            $this->messageManager->addErrorMessage('Blog posts can only be deleted with a form submission.');
            return $this->_redirect('*/*/');
        }

        $id = (int)$this->getRequest()->getParam('post_id');
        if (!$id) {
            $this->messageManager->addErrorMessage('Blog post ID is missing.');
            return $this->_redirect('*/*/');
        }

        $post = $this->postFactory->create()->load($id);
        if (!$post->getId()) {
            $this->messageManager->addErrorMessage('The requested blog post no longer exists.');
            return $this->_redirect('*/*/');
        }

        try {
            $identities = $post->getIdentities();
            $post->delete();
            $this->cache->clean($identities);
            $this->messageManager->addSuccessMessage('The blog post has been deleted.');
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $this->_redirect('*/*/');
    }
}
