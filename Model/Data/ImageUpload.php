<?php
declare(strict_types=1);

namespace Magecko\Blog\Model\Data;

use Magento\Framework\DataObject;
use Magecko\Blog\Api\Data\ImageUploadInterface;

class ImageUpload extends DataObject implements ImageUploadInterface
{
    public function getFileName()
    {
        return $this->getData(self::FILE_NAME) !== null ? (string)$this->getData(self::FILE_NAME) : null;
    }

    public function setFileName($fileName): ImageUploadInterface
    {
        return $this->setData(self::FILE_NAME, $fileName);
    }

    public function getContentBase64()
    {
        return $this->getData(self::CONTENT_BASE64) !== null ? (string)$this->getData(self::CONTENT_BASE64) : null;
    }

    public function setContentBase64($contentBase64): ImageUploadInterface
    {
        return $this->setData(self::CONTENT_BASE64, $contentBase64);
    }

    public function getMimeType()
    {
        return $this->getData(self::MIME_TYPE) !== null ? (string)$this->getData(self::MIME_TYPE) : null;
    }

    public function setMimeType($mimeType): ImageUploadInterface
    {
        return $this->setData(self::MIME_TYPE, $mimeType);
    }
}
