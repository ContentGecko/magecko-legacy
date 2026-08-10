<?php
declare(strict_types=1);

namespace Magecko\Blog\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Create the Magecko tables using the setup API available in Magento 2.2.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $postTableName = $setup->getTable('magecko_blog_post');
        if (!$setup->getConnection()->isTableExists($postTableName)) {
            $postTable = $setup->getConnection()->newTable($postTableName)
                ->addColumn(
                    'post_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'Post ID'
                )
                ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Title')
                ->addColumn('slug', Table::TYPE_TEXT, 255, ['nullable' => false], 'Slug')
                ->addColumn('status', Table::TYPE_TEXT, 16, ['nullable' => false, 'default' => 'draft'], 'Status')
                ->addColumn('topic', Table::TYPE_TEXT, 120, ['nullable' => true], 'Topic')
                ->addColumn('author', Table::TYPE_TEXT, 120, ['nullable' => true], 'Author')
                ->addColumn('publish_date', Table::TYPE_DATETIME, null, ['nullable' => true], 'Publish Date')
                ->addColumn('modified_date', Table::TYPE_DATETIME, null, ['nullable' => true], 'Modified Date')
                ->addColumn('featured_image', Table::TYPE_TEXT, 255, ['nullable' => true], 'Featured Image')
                ->addColumn(
                    'featured_image_alt',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Featured Image Alt Text'
                )
                ->addColumn('meta_title', Table::TYPE_TEXT, 255, ['nullable' => true], 'Meta Title')
                ->addColumn('meta_description', Table::TYPE_TEXT, '64k', ['nullable' => true], 'Meta Description')
                ->addColumn('canonical_url', Table::TYPE_TEXT, 255, ['nullable' => true], 'Canonical URL')
                ->addColumn('body_html', Table::TYPE_TEXT, '2M', ['nullable' => true], 'Body HTML')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )
                ->addIndex(
                    $setup->getIdxName(
                        'magecko_blog_post',
                        ['slug'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['slug'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addIndex($setup->getIdxName('magecko_blog_post', ['publish_date']), ['publish_date'])
                ->addIndex($setup->getIdxName('magecko_blog_post', ['status']), ['status'])
                ->addIndex($setup->getIdxName('magecko_blog_post', ['topic']), ['topic'])
                ->setComment('Magecko Blog Posts');

            $setup->getConnection()->createTable($postTable);
        }

        $translationTableName = $setup->getTable('magecko_blog_post_store');
        if (!$setup->getConnection()->isTableExists($translationTableName)) {
            $translationTable = $setup->getConnection()->newTable($translationTableName)
                ->addColumn(
                    'translation_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'Translation ID'
                )
                ->addColumn(
                    'post_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Post ID'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Store View ID'
                )
                ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => true], 'Translated Title')
                ->addColumn('slug', Table::TYPE_TEXT, 255, ['nullable' => true], 'Translated Slug')
                ->addColumn('topic', Table::TYPE_TEXT, 120, ['nullable' => true], 'Translated Topic')
                ->addColumn('author', Table::TYPE_TEXT, 120, ['nullable' => true], 'Translated Author')
                ->addColumn(
                    'featured_image_alt',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Translated Featured Image Alt Text'
                )
                ->addColumn('meta_title', Table::TYPE_TEXT, 255, ['nullable' => true], 'Translated Meta Title')
                ->addColumn(
                    'meta_description',
                    Table::TYPE_TEXT,
                    '64k',
                    ['nullable' => true],
                    'Translated Meta Description'
                )
                ->addColumn(
                    'canonical_url',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Translated Canonical URL'
                )
                ->addColumn('body_html', Table::TYPE_TEXT, '2M', ['nullable' => true], 'Translated Body HTML')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )
                ->addIndex(
                    $setup->getIdxName(
                        'magecko_blog_post_store',
                        ['post_id', 'store_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['post_id', 'store_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addIndex(
                    $setup->getIdxName(
                        'magecko_blog_post_store',
                        ['store_id', 'slug'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['store_id', 'slug'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addIndex($setup->getIdxName('magecko_blog_post_store', ['post_id']), ['post_id'])
                ->addIndex($setup->getIdxName('magecko_blog_post_store', ['store_id']), ['store_id'])
                ->addForeignKey(
                    $setup->getFkName(
                        'magecko_blog_post_store',
                        'post_id',
                        'magecko_blog_post',
                        'post_id'
                    ),
                    'post_id',
                    $postTableName,
                    'post_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $setup->getFkName('magecko_blog_post_store', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $setup->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Magecko Blog Post Store Translations');

            $setup->getConnection()->createTable($translationTable);
        }

        $setup->endSetup();
    }
}
