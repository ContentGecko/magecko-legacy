<?php
declare(strict_types=1);

namespace Magecko\Blog\Console\Command;

use DOMDocument;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magecko\Blog\Model\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompatibilityCheckCommand extends Command
{
    private $productMetadata;
    private $moduleList;
    private $moduleDirReader;
    private $storeManager;
    private $filesystem;
    private $config;
    private $resource;

    public function __construct(
        ProductMetadataInterface $productMetadata,
        ModuleListInterface $moduleList,
        ModuleDirReader $moduleDirReader,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        Config $config,
        ResourceConnection $resource,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
        $this->moduleDirReader = $moduleDirReader;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->resource = $resource;
    }

    protected function configure(): void
    {
        $this->setName('magecko:compatibility-check');
        $this->setDescription('Check Magecko environment, storefront routes, and competing blog modules.');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hasFailure = false;
        $output->writeln('<info>Magecko compatibility check</info>');

        $mediaWritable = $this->isMediaWritable();
        $environmentRows = [
            ['Magento', $this->productMetadata->getEdition() . ' ' . $this->productMetadata->getVersion()],
            ['PHP', PHP_VERSION],
            ['Media directory', $mediaWritable ? 'Writable' : 'Not writable'],
        ];
        (new Table($output))->setHeaders(['Environment', 'Value'])->setRows($environmentRows)->render();

        if (!$this->isSupportedPhpVersion()) {
            $output->writeln('<error>Magecko Legacy requires PHP 7.1.x.</error>');
            $hasFailure = true;
        }

        if ($this->productMetadata->getVersion() !== '2.2.6') {
            $output->writeln('<error>Magecko Legacy supports Magento 2.2.6 only.</error>');
            $hasFailure = true;
        }

        if (!$mediaWritable) {
            $output->writeln('<error>pub/media/magecko/blog is not writable.</error>');
            $hasFailure = true;
        }

        $blogModules = $this->getCompetingBlogModules();
        $output->writeln('');
        if ($blogModules) {
            $output->writeln('<comment>Other enabled blog modules: ' . implode(', ', $blogModules) . '</comment>');
        } else {
            $output->writeln('<info>No other enabled blog modules were detected.</info>');
        }

        $storeRows = [];
        foreach ($this->storeManager->getStores(false) as $store) {
            $storeId = (int)$store->getId();
            $enabled = $this->config->isStorefrontEnabled($storeId);
            $route = $this->config->getRoute($storeId);
            $routeIsValid = Config::isValidRoute($route);
            $routeOwners = $routeIsValid
                ? array_merge(
                    $this->getModuleRouteOwners($route),
                    $this->getDatabaseRouteOwners($route, $storeId)
                )
                : [];
            $status = 'Ready';

            if (!$routeIsValid) {
                $status = 'Invalid route';
                $hasFailure = true;
            } elseif (!$enabled) {
                $status = $routeOwners ? 'Safe while disabled; route already used' : 'Disabled';
            } elseif ($routeOwners) {
                $status = 'Conflict: ' . implode(', ', $routeOwners);
                $hasFailure = true;
            }

            $storeRows[] = [
                $store->getCode(),
                $enabled ? 'Yes' : 'No',
                '/' . $route,
                $status,
            ];
        }

        $output->writeln('');
        (new Table($output))
            ->setHeaders(['Store view', 'Storefront enabled', 'Route', 'Result'])
            ->setRows($storeRows)
            ->render();

        if ($hasFailure) {
            $output->writeln('<error>Compatibility check failed. Resolve the errors before enabling Magecko.</error>');
            return 1;
        }

        $output->writeln('<info>Compatibility check passed.</info>');
        return 0;
    }

    private function isSupportedPhpVersion(): bool
    {
        return version_compare(PHP_VERSION, '7.1.0', '>=')
            && version_compare(PHP_VERSION, '7.2.0', '<');
    }

    private function isMediaWritable(): bool
    {
        try {
            $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $mediaDirectory->create('magecko/blog');
            return $mediaDirectory->isWritable('magecko/blog');
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function getCompetingBlogModules(): array
    {
        $modules = array_filter(
            $this->moduleList->getNames(),
            static function (string $module): bool {
                return $module !== 'Magecko_Blog' && stripos($module, 'blog') !== false;
            }
        );
        sort($modules, SORT_NATURAL | SORT_FLAG_CASE);
        return array_values($modules);
    }

    private function getModuleRouteOwners(string $route): array
    {
        $owners = [];
        foreach ($this->moduleList->getNames() as $moduleName) {
            if ($moduleName === 'Magecko_Blog') {
                continue;
            }

            try {
                $routeFile = $this->moduleDirReader->getModuleDir('etc', $moduleName)
                    . '/frontend/routes.xml';
            } catch (\InvalidArgumentException $exception) {
                continue;
            }

            if (!is_file($routeFile)) {
                continue;
            }

            $previousErrorMode = libxml_use_internal_errors(true);
            $loaded = false;
            try {
                $document = new DOMDocument();
                $loaded = $document->load($routeFile, LIBXML_NONET);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previousErrorMode);
            }

            if (!$loaded) {
                continue;
            }

            foreach ($document->getElementsByTagName('route') as $routeNode) {
                if ($routeNode->getAttribute('frontName') === $route) {
                    $owners[] = $moduleName;
                    break;
                }
            }
        }

        sort($owners, SORT_NATURAL | SORT_FLAG_CASE);
        return $owners;
    }

    private function getDatabaseRouteOwners(string $route, int $storeId): array
    {
        $connection = $this->resource->getConnection();
        $owners = [];

        $rewriteSelect = $connection->select()
            ->from($this->resource->getTableName('url_rewrite'), ['entity_type'])
            ->where('store_id = ?', $storeId)
            ->where('request_path IN (?)', [$route, $route . '/'])
            ->distinct();
        foreach ($connection->fetchCol($rewriteSelect) as $entityType) {
            $owners[] = 'URL rewrite (' . (string)$entityType . ')';
        }

        $pageTable = $this->resource->getTableName('cms_page');
        $pageStoreTable = $this->resource->getTableName('cms_page_store');
        $pageSelect = $connection->select()
            ->from(['page' => $pageTable], ['identifier'])
            ->join(
                ['page_store' => $pageStoreTable],
                'page.page_id = page_store.page_id',
                []
            )
            ->where('page.identifier = ?', $route)
            ->where('page.is_active = ?', 1)
            ->where('page_store.store_id IN (?)', [0, $storeId])
            ->limit(1);
        if ($connection->fetchOne($pageSelect)) {
            $owners[] = 'CMS page';
        }

        return $owners;
    }
}
