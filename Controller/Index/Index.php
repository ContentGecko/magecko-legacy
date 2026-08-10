<?php
declare(strict_types=1);

namespace Magecko\Blog\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magecko\Blog\Model\Config;

class Index extends Action
{
    private $resultPageFactory;
    private $forwardFactory;
    private $config;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $forwardFactory,
        Config $config
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->config = $config;
    }

    public function execute()
    {
        if (!$this->config->isStorefrontEnabled() || !$this->getRequest()->getParam('_magecko_routed')) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->set('Blog');
        return $page;
    }
}
