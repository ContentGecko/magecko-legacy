<?php
declare(strict_types=1);

namespace Magecko\Blog\Api\Data;

interface ImageUploadResultInterface
{
    public const PATH = 'path';
    public const URL = 'url';

    /**
     * @return string|null
     */
    public function getPath();

    /**
     * @param string|null $path
     * @return \Magecko\Blog\Api\Data\ImageUploadResultInterface
     */
    public function setPath($path): ImageUploadResultInterface;

    /**
     * @return string|null
     */
    public function getUrl();

    /**
     * @param string|null $url
     * @return \Magecko\Blog\Api\Data\ImageUploadResultInterface
     */
    public function setUrl($url): ImageUploadResultInterface;
}
