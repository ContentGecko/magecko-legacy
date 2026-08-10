<?php
declare(strict_types=1);

namespace Magecko\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magecko\Blog\Controller\Adminhtml\Post;

class Index extends Post
{
    private $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $page->setActiveMenu('Magecko_Blog::posts');
        $page->getConfig()->getTitle()->prepend('Blog Posts');
        return $page;
    }
}
