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
    public function getPath(): ?string;

    /**
     * @param string|null $path
     * @return \Magecko\Blog\Api\Data\ImageUploadResultInterface
     */
    public function setPath(?string $path): ImageUploadResultInterface;

    /**
     * @return string|null
     */
    public function getUrl(): ?string;

    /**
     * @param string|null $url
     * @return \Magecko\Blog\Api\Data\ImageUploadResultInterface
     */
    public function setUrl(?string $url): ImageUploadResultInterface;
}
