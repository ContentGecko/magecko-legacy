<?php
declare(strict_types=1);

namespace Magecko\Blog\Model;

use Magento\Framework\Api\SearchResults;
use Magecko\Blog\Api\Data\PostSearchResultsInterface;

class PostSearchResults extends SearchResults implements PostSearchResultsInterface
{
}
