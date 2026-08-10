<?php
declare(strict_types=1);

namespace Magecko\Blog\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PostSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Magecko\Blog\Api\Data\PostInterface[]
     */
    public function getItems();

    /**
     * @param \Magecko\Blog\Api\Data\PostInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
