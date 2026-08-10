<?php
declare(strict_types=1);

namespace Magecko\Blog\Api;

use Magecko\Blog\Api\Data\ImageUploadInterface;
use Magecko\Blog\Api\Data\ImageUploadResultInterface;

interface MediaManagementInterface
{
    /**
     * Upload a base64-encoded blog image into pub/media/magecko/blog.
     *
     * @param \Magecko\Blog\Api\Data\ImageUploadInterface $image
     * @return \Magecko\Blog\Api\Data\ImageUploadResultInterface
     */
    public function upload(ImageUploadInterface $image): ImageUploadResultInterface;
}
