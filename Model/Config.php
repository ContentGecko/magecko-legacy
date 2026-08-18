<?php
declare(strict_types=1);

namespace Magecko\Blog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_STOREFRONT_ENABLED = 'magecko_blog/storefront/enabled';
    public const XML_PATH_ROUTE = 'magecko_blog/storefront/route';
    public const XML_PATH_HEADING = 'magecko_blog/storefront/heading';
    public const XML_PATH_INTRO = 'magecko_blog/storefront/intro';
    public const DEFAULT_ROUTE = 'blog';
    public const DEFAULT_HEADING = 'Blog';

    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isStorefrontEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_STOREFRONT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getRoute(?int $storeId = null): string
    {
        $route = (string)$this->scopeConfig->getValue(
            self::XML_PATH_ROUTE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return self::normalizeRoute($route) ?: self::DEFAULT_ROUTE;
    }

    public function getHeading(?int $storeId = null): string
    {
        $heading = trim((string)$this->scopeConfig->getValue(
            self::XML_PATH_HEADING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));

        return $heading !== '' ? $heading : self::DEFAULT_HEADING;
    }

    public function getIntro(?int $storeId = null): string
    {
        return trim((string)$this->scopeConfig->getValue(
            self::XML_PATH_INTRO,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
    }

    public static function normalizeRoute(string $route): string
    {
        return strtolower(trim(trim($route), '/'));
    }

    public static function isValidRoute(string $route): bool
    {
        return (bool)preg_match('/^[a-z0-9][a-z0-9-]{0,63}$/', self::normalizeRoute($route));
    }
}
