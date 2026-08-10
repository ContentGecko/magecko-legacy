<?php
declare(strict_types=1);

namespace Magecko\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Post extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('magecko_blog_post', 'post_id');
    }
}
