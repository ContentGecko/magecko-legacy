<?php
declare(strict_types=1);

namespace Magecko\Blog\Api\Data;

interface ImageUploadInterface
{
    public const FILE_NAME = 'file_name';
    public const CONTENT_BASE64 = 'content_base64';
    public const MIME_TYPE = 'mime_type';

    /**
     * @return string|null
     */
    public function getFileName();

    /**
     * @param string|null $fileName
     * @return \Magecko\Blog\Api\Data\ImageUploadInterface
     */
    public function setFileName($fileName): ImageUploadInterface;

    /**
     * @return string|null
     */
    public function getContentBase64();

    /**
     * @param string|null $contentBase64
     * @return \Magecko\Blog\Api\Data\ImageUploadInterface
     */
    public function setContentBase64($contentBase64): ImageUploadInterface;

    /**
     * @return string|null
     */
    public function getMimeType();

    /**
     * @param string|null $mimeType
     * @return \Magecko\Blog\Api\Data\ImageUploadInterface
     */
    public function setMimeType($mimeType): ImageUploadInterface;
}
