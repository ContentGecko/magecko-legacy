<?php
declare(strict_types=1);

namespace Magecko\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magecko\Blog\Controller\Adminhtml\Post;
use Magecko\Blog\Model\PostFactory;

class Edit extends Post
{
    private $resultPageFactory;
    private $postFactory;
    private $registry;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        PostFactory $postFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->postFactory = $postFactory;
        $this->registry = $registry;
    }

    public function execute()
    {
        $post = $this->postFactory->create();
        $id = (int)$this->getRequest()->getParam('post_id');

        if ($id) {
            $post->load($id);
            if (!$post->getId()) {
                $this->messageManager->addErrorMessage('The requested blog post no longer exists.');
                return $this->_redirect('*/*/');
            }
        }

        $this->registry->register('magecko_blog_post', $post);

        $page = $this->resultPageFactory->create();
        $page->setActiveMenu('Magecko_Blog::posts');
        $page->getConfig()->getTitle()->prepend($post->getId() ? 'Edit Blog Post' : 'New Blog Post');
        return $page;
    }
}
