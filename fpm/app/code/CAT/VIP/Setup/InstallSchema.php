<?php

namespace CAT\VIP\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cat_viporders'))
            ->addColumn(
                'entity_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_TEXT,
                15,
                ['nullable' => false],
                'Customer ID'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_TEXT,
                15,
                ['nullable' => false],
                'Product ID'
            )
            ->addColumn(
                'qty',
                Table::TYPE_TEXT,
                11,
                ['nullable' => false],
                'Product ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_TEXT,
                11,
                ['nullable' => false],
                'Qoute ID'
            )
            ->addColumn('status', Table::TYPE_SMALLINT, 4, ['nullable' => false, 'default' => '1'], 'Status')
            ->addColumn(
                'creation_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )->addColumn(
                'update_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Update Time'
            );
        $installer->getConnection()->createTable($table);

        $table1 = $installer->getConnection()
            ->newTable($installer->getTable('cat_vipproducts'))
            ->addColumn(
                'entity_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'vendor_id',
                Table::TYPE_TEXT,
                15,
                ['nullable' => false],
                'Vendor ID'
            )
            ->addColumn(
                'customer_group',
                Table::TYPE_TEXT,
                15,
                ['nullable' => false],
                'Customer Group'
            )
             ->addColumn(
                'product_id',
                Table::TYPE_TEXT,
                15,
                ['nullable' => false],
                'Product ID'
            )
            ->addColumn(
                'global_qty',
                Table::TYPE_TEXT,
                11,
                ['nullable' => false],
                'global_qty'
            )
            ->addColumn(
                'ind_qty',
                Table::TYPE_TEXT,
                11,
                ['nullable' => false],
                'ind_qty'
            )
            ->addColumn(
                'discount_type',
                Table::TYPE_TEXT,
                11,
                ['nullable' => false],
                'discount_type'
            )
            ->addColumn(
                'discount',
                Table::TYPE_TEXT,
                11,
                ['nullable' => false],
                'discount'
            )
            ->addColumn(
                'creation_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )->addColumn(
                'update_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Update Time'
            );
        $installer->getConnection()->createTable($table1);
        
        $installer->endSetup();
    }
}
