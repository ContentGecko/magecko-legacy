<?php
declare(strict_types=1);

namespace Magecko\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magecko\Blog\Controller\Adminhtml\Post;
use Magecko\Blog\Model\Post as BlogPost;
use Magecko\Blog\Model\PostFactory;
use Magecko\Blog\Model\PostTranslation;

class Save extends Post
{
    private const MAX_IMAGE_BYTES = 5242880;
    private const IMAGE_MIME_TYPES = ['image/gif', 'image/jpeg', 'image/png', 'image/webp'];

    private $postFactory;
    private $dataPersistor;
    private $dateTime;
    private $uploaderFactory;
    private $filesystem;
    private $cache;
    private $postTranslation;

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        DataPersistorInterface $dataPersistor,
        DateTime $dateTime,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        CacheInterface $cache,
        PostTranslation $postTranslation
    ) {
        parent::__construct($context);
        $this->postFactory = $postFactory;
        $this->dataPersistor = $dataPersistor;
        $this->dateTime = $dateTime;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->cache = $cache;
        $this->postTranslation = $postTranslation;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $this->_redirect('*/*/');
        }

        $post = $this->postFactory->create();
        $id = (int)($data['post_id'] ?? 0);
        if ($id) {
            $post->load($id);
            if (!$post->getId()) {
                $this->messageManager->addErrorMessage('The requested blog post no longer exists.');
                return $this->_redirect('*/*/');
            }
        }

        $slug = $this->normalizeSlug((string)($data['slug'] ?? $data['title'] ?? ''));
        $now = $this->dateTime->gmtDate('Y-m-d H:i:s');
        $featuredImage = trim((string)($data['featured_image'] ?? $post->getData('featured_image') ?? ''));
        if (!empty($data['remove_featured_image'])) {
            $featuredImage = '';
        }

        $uploadedFeaturedImage = $this->uploadImage('featured_image_file');
        if ($uploadedFeaturedImage) {
            $featuredImage = $uploadedFeaturedImage;
        }

        $bodyHtml = trim((string)($data['body_html'] ?? ''));

        $post->addData([
            'title' => trim((string)($data['title'] ?? '')),
            'slug' => $slug,
            'status' => $this->normalizeStatus((string)($data['status'] ?? '')),
            'topic' => trim((string)($data['topic'] ?? '')),
            'author' => trim((string)($data['author'] ?? '')),
            'publish_date' => $this->normalizeDate((string)($data['publish_date'] ?? '')) ?: $now,
            'modified_date' => $this->normalizeDate((string)($data['modified_date'] ?? '')) ?: $now,
            'featured_image' => $featuredImage,
            'featured_image_alt' => trim((string)($data['featured_image_alt'] ?? '')),
            'meta_title' => trim((string)($data['meta_title'] ?? '')),
            'meta_description' => trim((string)($data['meta_description'] ?? '')),
            'canonical_url' => $this->normalizeCanonicalUrl((string)($data['canonical_url'] ?? '')),
            'body_html' => $bodyHtml,
        ]);

        try {
            $this->validate($post->getData());
            $post->save();
            $this->saveTranslations((int)$post->getId(), (array)($data['translations'] ?? []));
            $this->cache->clean($post->getIdentities());
            $this->dataPersistor->clear('magecko_blog_post');
            $this->messageManager->addSuccessMessage('The blog post has been saved.');

            if ($this->getRequest()->getParam('back')) {
                return $this->_redirect('*/*/edit', ['post_id' => $post->getId()]);
            }

            return $this->_redirect('*/*/');
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->dataPersistor->set('magecko_blog_post', $data);
            return $this->_redirect('*/*/edit', ['post_id' => $id]);
        }
    }

    private function validate(array $data): void
    {
        if ($data['title'] === '') {
            throw new \InvalidArgumentException('Title is required.');
        }

        if ($data['slug'] === '') {
            throw new \InvalidArgumentException('Slug is required.');
        }

        if (!in_array((string)($data['status'] ?? ''), BlogPost::STATUSES, true)) {
            throw new \InvalidArgumentException('Status must be draft or published.');
        }

        if ($data['body_html'] === '') {
            throw new \InvalidArgumentException('Article body is required.');
        }
    }

    private function saveTranslations(int $postId, array $translations): void
    {
        foreach ($translations as $storeId => $translation) {
            $storeId = (int)$storeId;
            if ($storeId <= 0 || !is_array($translation)) {
                continue;
            }

            $bodyHtml = trim((string)($translation['body_html'] ?? ''));
            $this->postTranslation->save($postId, $storeId, [
                'title' => trim((string)($translation['title'] ?? '')),
                'slug' => $this->normalizeSlug((string)($translation['slug'] ?? '')),
                'topic' => trim((string)($translation['topic'] ?? '')),
                'author' => trim((string)($translation['author'] ?? '')),
                'featured_image_alt' => trim((string)($translation['featured_image_alt'] ?? '')),
                'meta_title' => trim((string)($translation['meta_title'] ?? '')),
                'meta_description' => trim((string)($translation['meta_description'] ?? '')),
                'canonical_url' => $this->normalizeCanonicalUrl((string)($translation['canonical_url'] ?? '')),
                'body_html' => $bodyHtml,
            ]);
        }
    }

    private function uploadImage(string $fileId): ?string
    {
        $file = $this->getUploadedFileData($fileId);
        if (empty($file['name']) || (int)($file['error'] ?? UPLOAD_ERR_OK) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $targetDirectory = 'magecko/blog';
        $mediaDirectory->create($targetDirectory);

        $this->validateUploadedImageFile($fileId);

        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'webp']);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($mediaDirectory->getAbsolutePath($targetDirectory));

        if (empty($result['file'])) {
            throw new \RuntimeException('The image upload did not return a saved file.');
        }

        return $targetDirectory . '/' . ltrim((string)$result['file'], '/');
    }

    private function validateUploadedImageFile(string $fileId): void
    {
        $file = $this->getUploadedFileData($fileId);
        $size = (int)($file['size'] ?? 0);
        if ($size > self::MAX_IMAGE_BYTES) {
            throw new \RuntimeException('Featured image files must be 5 MB or smaller.');
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_readable($tmpName)) {
            throw new \RuntimeException('The uploaded featured image could not be validated.');
        }

        $imageInfo = getimagesizefromstring((string)file_get_contents($tmpName));
        if (!is_array($imageInfo) || empty($imageInfo['mime'])) {
            throw new \RuntimeException('Featured image must be a valid image file.');
        }

        if (!in_array(strtolower((string)$imageInfo['mime']), self::IMAGE_MIME_TYPES, true)) {
            throw new \RuntimeException('Featured image type must be JPG, PNG, GIF, or WebP.');
        }
    }

    private function getUploadedFileData(string $fileId): array
    {
        $files = (array)$this->getRequest()->getFiles();
        $file = $files[$fileId] ?? [];

        return is_array($file) ? $file : [];
    }

    private function normalizeSlug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?: '';
        return trim($value, '-');
    }

    private function normalizeStatus(string $value): string
    {
        $value = strtolower(trim($value));
        return in_array($value, BlogPost::STATUSES, true) ? $value : BlogPost::STATUS_DRAFT;
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return (new \DateTime($value))->format('Y-m-d H:i:s');
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function normalizeCanonicalUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return preg_match('#^https?://#i', $value) ? $value : '';
    }
}
