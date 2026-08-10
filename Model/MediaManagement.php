<?php
declare(strict_types=1);

namespace Magecko\Blog\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magecko\Blog\Api\Data\ImageUploadInterface;
use Magecko\Blog\Api\Data\ImageUploadResultInterface;
use Magecko\Blog\Api\Data\ImageUploadResultInterfaceFactory;
use Magecko\Blog\Api\MediaManagementInterface;

class MediaManagement implements MediaManagementInterface
{
    private const TARGET_DIRECTORY = 'magecko/blog';
    private const EXTENSIONS = ['jpg', 'jpeg', 'gif', 'png', 'webp'];
    private const MAX_IMAGE_BYTES = 5242880;
    private const MIME_TYPES = [
        'gif' => 'image/gif',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
    ];

    private $filesystem;
    private $storeManager;
    private $resultFactory;

    public function __construct(
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        ImageUploadResultInterfaceFactory $resultFactory
    ) {
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->resultFactory = $resultFactory;
    }

    public function upload(ImageUploadInterface $image): ImageUploadResultInterface
    {
        $fileName = $this->sanitizeFileName((string)$image->getFileName());
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($extension, self::EXTENSIONS, true)) {
            throw new InputException(__('Allowed image extensions are: %1.', implode(', ', self::EXTENSIONS)));
        }

        $content = base64_decode((string)$image->getContentBase64(), true);
        if ($content === false || $content === '') {
            throw new InputException(__('Image content must be valid base64.'));
        }
        $this->validateImageContent($content, $extension, (string)$image->getMimeType());

        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->create(self::TARGET_DIRECTORY);
        $relativePath = self::TARGET_DIRECTORY . '/' . $this->getAvailableFileName($fileName);

        try {
            $mediaDirectory->writeFile($relativePath, $content);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()), $exception);
        }

        $result = $this->resultFactory->create();
        $result->setPath($relativePath);
        $result->setUrl($this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $relativePath);
        return $result;
    }

    private function sanitizeFileName(string $fileName): string
    {
        $fileName = basename($fileName);
        $fileName = strtolower(preg_replace('/[^a-zA-Z0-9._-]+/', '-', $fileName) ?: '');
        $fileName = trim($fileName, '.-');
        if ($fileName === '' || strpos($fileName, '.') === false) {
            throw new InputException(__('A file name with an extension is required.'));
        }

        return $fileName;
    }

    private function validateImageContent(string $content, string $extension, string $declaredMimeType): void
    {
        if (strlen($content) > self::MAX_IMAGE_BYTES) {
            throw new InputException(__('Image files must be 5 MB or smaller.'));
        }

        $declaredMimeType = strtolower(trim($declaredMimeType));
        $expectedMimeType = self::MIME_TYPES[$extension] ?? '';
        if ($declaredMimeType !== '' && $declaredMimeType !== $expectedMimeType) {
            throw new InputException(__('The declared MIME type does not match the file extension.'));
        }

        set_error_handler(static function (): bool {
            return true;
        });

        try {
            $imageInfo = getimagesizefromstring($content);
        } finally {
            restore_error_handler();
        }

        if (!is_array($imageInfo) || empty($imageInfo['mime'])) {
            throw new InputException(__('Uploaded content must be a valid image file.'));
        }

        if (strtolower((string)$imageInfo['mime']) !== $expectedMimeType) {
            throw new InputException(__('Image content does not match the file extension.'));
        }
    }

    private function getAvailableFileName(string $fileName): string
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $candidate = $fileName;
        $counter = 1;

        while ($mediaDirectory->isExist(self::TARGET_DIRECTORY . '/' . $candidate)) {
            $candidate = $baseName . '-' . $counter . '.' . $extension;
            $counter++;
        }

        return $candidate;
    }
}
