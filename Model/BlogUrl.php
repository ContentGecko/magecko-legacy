<?php
declare(strict_types=1);

namespace Magecko\Blog\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class BlogUrl
{
    private $config;
    private $storeManager;

    public function __construct(Config $config, StoreManagerInterface $storeManager)
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    public function getLandingUrl(?int $storeId = null, array $query = []): string
    {
        $storeId = $this->resolveStoreId($storeId);
        $url = rtrim($this->getStoreBaseUrl($storeId), '/') . '/' . $this->config->getRoute($storeId);

        return $query ? $url . '?' . http_build_query($query) : $url;
    }

    public function getPostUrl(string $slug, ?int $storeId = null): string
    {
        $storeId = $this->resolveStoreId($storeId);
        return $this->getLandingUrl($storeId) . '/' . ltrim($slug, '/');
    }

    private function resolveStoreId(?int $storeId): int
    {
        if ($storeId !== null && $storeId > 0) {
            return $storeId;
        }

        try {
            $currentStoreId = (int)$this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $currentStoreId = 0;
        }

        return $currentStoreId > 0
            ? $currentStoreId
            : (int)$this->storeManager->getDefaultStoreView()->getId();
    }

    private function getStoreBaseUrl(int $storeId): string
    {
        try {
            return $this->storeManager->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_LINK);
        } catch (NoSuchEntityException $exception) {
            return $this->storeManager->getDefaultStoreView()->getBaseUrl(UrlInterface::URL_TYPE_LINK);
        }
    }
}
