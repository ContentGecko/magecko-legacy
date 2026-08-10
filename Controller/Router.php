<?php
declare(strict_types=1);

namespace Magecko\Blog\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Url;
use Magecko\Blog\Model\Config;

class Router implements RouterInterface
{
    private $actionFactory;
    private $config;

    public function __construct(ActionFactory $actionFactory, Config $config)
    {
        $this->actionFactory = $actionFactory;
        $this->config = $config;
    }

    public function match(RequestInterface $request)
    {
        if ($request->getModuleName() === 'magecko_blog' || !$this->config->isStorefrontEnabled()) {
            return null;
        }

        $identifier = trim($request->getPathInfo(), '/');
        $route = $this->config->getRoute();
        if (!Config::isValidRoute($route)) {
            return null;
        }

        if ($identifier === $route) {
            $request
                ->setModuleName('magecko_blog')
                ->setControllerName('index')
                ->setActionName('index')
                ->setParam('_magecko_routed', 1)
                ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

            return $this->actionFactory->create(Forward::class);
        }

        $pattern = '/^' . preg_quote($route, '/') . '\/([a-z0-9][a-z0-9-]*)$/';
        if (preg_match($pattern, $identifier, $matches)) {
            $request
                ->setModuleName('magecko_blog')
                ->setControllerName('post')
                ->setActionName('view')
                ->setParam('slug', $matches[1])
                ->setParam('_magecko_routed', 1)
                ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

            return $this->actionFactory->create(Forward::class);
        }

        return null;
    }
}
