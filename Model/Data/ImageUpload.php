<?php
declare(strict_types=1);

namespace Magecko\Blog\Model\Data;

use Magento\Framework\DataObject;
use Magecko\Blog\Api\Data\ImageUploadInterface;

class ImageUpload extends DataObject implements ImageUploadInterface
{
    public function getFileName(): ?string
    {
        return $this->getData(self::FILE_NAME) !== null ? (string)$this->getData(self::FILE_NAME) : null;
    }

    public function setFileName(?string $fileName): ImageUploadInterface
    {
        return $this->setData(self::FILE_NAME, $fileName);
    }

    public function getContentBase64(): ?string
    {
        return $this->getData(self::CONTENT_BASE64) !== null ? (string)$this->getData(self::CONTENT_BASE64) : null;
    }

    public function setContentBase64(?string $contentBase64): ImageUploadInterface
    {
        return $this->setData(self::CONTENT_BASE64, $contentBase64);
    }

    public function getMimeType(): ?string
    {
        return $this->getData(self::MIME_TYPE) !== null ? (string)$this->getData(self::MIME_TYPE) : null;
    }

    public function setMimeType(?string $mimeType): ImageUploadInterface
    {
        return $this->setData(self::MIME_TYPE, $mimeType);
    }
}
