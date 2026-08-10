<?php
declare(strict_types=1);

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\State;
use Magento\Framework\Registry;
use Magecko\Blog\Api\MediaManagementInterface;
use Magecko\Blog\Api\PostRepositoryInterface;
use Magecko\Blog\Block\Adminhtml\Post\Edit as AdminEditBlock;
use Magecko\Blog\Block\Adminhtml\Post\Index as AdminIndexBlock;
use Magecko\Blog\Model\Data\ImageUpload;
use Magecko\Blog\Model\Config;
use Magecko\Blog\Model\Post;
use Magecko\Blog\Model\PostFactory;
use Magecko\Blog\Model\ResourceModel\Post\CollectionFactory;

function mageckoFindMagentoRoot(string $start): string
{
    $dir = $start;
    while ($dir !== dirname($dir)) {
        if (is_file($dir . '/app/bootstrap.php')) {
            return $dir;
        }
        $dir = dirname($dir);
    }

    throw new RuntimeException('Could not find Magento app/bootstrap.php.');
}

function mageckoAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$root = mageckoFindMagentoRoot(__DIR__);
require $root . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

try {
    $objectManager->get(State::class)->setAreaCode('adminhtml');
} catch (Throwable $exception) {
    // Area code may already be set when this script is called from another runner.
}

/** @var ResourceConnection $resource */
$resource = $objectManager->get(ResourceConnection::class);
$connection = $resource->getConnection();
$postTable = $resource->getTableName('magecko_blog_post');
$prefix = 'magecko-smoke-' . date('YmdHis') . '-' . bin2hex(random_bytes(3));
$mediaPaths = [];

$cleanup = static function () use ($connection, $postTable, $prefix, &$mediaPaths, $objectManager): void {
    $connection->delete($postTable, ['slug LIKE ?' => $prefix . '%']);
    if (!$mediaPaths) {
        return;
    }

    /** @var Filesystem $filesystem */
    $filesystem = $objectManager->get(Filesystem::class);
    $mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    foreach ($mediaPaths as $path) {
        if ($mediaDirectory->isExist($path)) {
            $mediaDirectory->delete($path);
        }
    }
};

$cleanup();

try {
    /** @var PostFactory $postFactory */
    $postFactory = $objectManager->get(PostFactory::class);
    /** @var PostRepositoryInterface $repository */
    $repository = $objectManager->get(PostRepositoryInterface::class);

    $createPost = static function (int $index, string $status) use ($postFactory, $repository, $prefix): Post {
        /** @var Post $post */
        $post = $postFactory->create();
        $post->setTitle('Magecko Smoke ' . $index);
        $post->setSlug($prefix . '-' . $index);
        $post->setStatus($status);
        $post->setTopic('Smoke Topic');
        $post->setAuthor('Magecko Smoke');
        $post->setPublishDate('2026-07-07 12:00:00');
        $post->setModifiedDate('2026-07-07 12:00:00');
        $post->setBodyHtml('<p>Magecko smoke body ' . $index . '.</p>');

        return $repository->save($post);
    };

    for ($i = 1; $i <= 10; $i++) {
        $createPost($i, Post::STATUS_PUBLISHED);
    }
    $createPost(11, Post::STATUS_DRAFT);

    /** @var CollectionFactory $collectionFactory */
    $collectionFactory = $objectManager->get(CollectionFactory::class);
    $publicCollection = $collectionFactory->create();
    $publicCollection->addPublishedFilter();
    $publicCollection->addTitleFilter('Magecko Smoke');
    $publicCollection->addTopicFilter('Smoke Topic');
    $publicCollection->addAuthorFilter('Magecko Smoke');
    mageckoAssert((int)$publicCollection->getSize() === 10, 'Published collection should exclude drafts.');

    $pagedCollection = $collectionFactory->create();
    $pagedCollection->addPublishedFilter();
    $pagedCollection->addTitleFilter('Magecko Smoke');
    $pagedCollection->setOrder('post_id', 'ASC');
    $pagedCollection->setPageSize(4);
    $pagedCollection->setCurPage(3);
    mageckoAssert(count($pagedCollection->getItems()) === 2, 'Paged collection should return the final two posts.');

    /** @var RequestInterface $request */
    $request = $objectManager->get(RequestInterface::class);
    $request->setParams([
        'title' => 'Magecko Smoke',
        'status' => Post::STATUS_PUBLISHED,
        'topic' => 'Smoke Topic',
        'author' => 'Magecko Smoke',
        'limit' => 20,
        'p' => 1,
    ]);

    /** @var AdminIndexBlock $adminBlock */
    $adminBlock = $objectManager->create(AdminIndexBlock::class);
    mageckoAssert(
        (int)$adminBlock->getPosts()->getSize() === 10,
        'Admin filters should return only matching published posts.'
    );
    mageckoAssert(
        strpos($adminBlock->toHtml(), 'Magecko Smoke') !== false,
        'Admin post listing template should render on Magento 2.2.'
    );

    /** @var Registry $registry */
    $registry = $objectManager->get(Registry::class);
    $editPost = $repository->getBySlug($prefix . '-1');
    $registry->register('magecko_blog_post', $editPost, true);
    /** @var AdminEditBlock $editBlock */
    $editBlock = $objectManager->create(AdminEditBlock::class);
    $editHtml = $editBlock->toHtml();
    mageckoAssert(
        strpos($editHtml, 'value="Magecko&#x20;Smoke&#x20;1"') !== false,
        'Admin post editor should render saved data.'
    );
    mageckoAssert(
        strpos($editHtml, 'tiny_mce/setup') !== false,
        'Admin post editor should load Magento 2.2 WYSIWYG configuration.'
    );
    $registry->unregister('magecko_blog_post');

    /** @var MediaManagementInterface $mediaManagement */
    $mediaManagement = $objectManager->get(MediaManagementInterface::class);

    /** @var Config $config */
    $config = $objectManager->get(Config::class);
    mageckoAssert(Config::isValidRoute($config->getRoute()), 'Configured storefront route should be valid.');

    /** @var ImageUpload $invalidImage */
    $invalidImage = $objectManager->create(ImageUpload::class);
    $invalidImage->setFileName($prefix . '-invalid.png');
    $invalidImage->setMimeType('image/png');
    $invalidImage->setContentBase64(base64_encode('not an image'));
    try {
        $mediaManagement->upload($invalidImage);
        mageckoAssert(false, 'Invalid image content should be rejected.');
    } catch (InputException $exception) {
        mageckoAssert(true, 'Invalid image content was rejected.');
    }

    /** @var ImageUpload $validImage */
    $validImage = $objectManager->create(ImageUpload::class);
    $validImage->setFileName($prefix . '-valid.png');
    $validImage->setMimeType('image/png');
    $validImage->setContentBase64(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
    );
    $uploadResult = $mediaManagement->upload($validImage);
    $mediaPaths[] = $uploadResult->getPath();
    mageckoAssert(
        strpos((string)$uploadResult->getPath(), 'magecko/blog/' . $prefix . '-valid') === 0,
        'Valid media upload should return a Magecko media path.'
    );

    fwrite(STDOUT, "Magecko integration smoke passed.\n");
} finally {
    $cleanup();
}
