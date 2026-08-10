<?php
declare(strict_types=1);

namespace Magecko\Blog\Model\Data;

use Magento\Framework\DataObject;
use Magecko\Blog\Api\Data\ImageUploadResultInterface;

class ImageUploadResult extends DataObject implements ImageUploadResultInterface
{
    public function getPath(): ?string
    {
        return $this->getData(self::PATH) !== null ? (string)$this->getData(self::PATH) : null;
    }

    public function setPath(?string $path): ImageUploadResultInterface
    {
        return $this->setData(self::PATH, $path);
    }

    public function getUrl(): ?string
    {
        return $this->getData(self::URL) !== null ? (string)$this->getData(self::URL) : null;
    }

    public function setUrl(?string $url): ImageUploadResultInterface
    {
        return $this->setData(self::URL, $url);
    }
}
