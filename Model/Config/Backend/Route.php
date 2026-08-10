<?php
declare(strict_types=1);

namespace Magecko\Blog\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;
use Magecko\Blog\Model\Config;

class Route extends Value
{
    private const RESERVED_ROUTES = [
        'admin',
        'graphql',
        'media',
        'magecko_blog',
        'rest',
        'static',
    ];

    public function beforeSave()
    {
        $route = Config::normalizeRoute((string)$this->getValue());
        if (!Config::isValidRoute($route)) {
            throw new LocalizedException(
                __('Use 1-64 lowercase letters, numbers, or hyphens for the Magecko frontend route.')
            );
        }

        if (in_array($route, self::RESERVED_ROUTES, true)) {
            throw new LocalizedException(__('The route "%1" is reserved. Choose another route.', $route));
        }

        $this->setValue($route);
        return parent::beforeSave();
    }
}
