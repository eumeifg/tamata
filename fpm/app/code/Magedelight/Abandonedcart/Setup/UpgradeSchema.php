<?php
namespace Magedelight\Abandonedcart\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    const TBL_MYCUSTOMREPORT_DAILY = 'md_abandonedcart_report_daily';
    const TBL_MYCUSTOMREPORT_MONTHLY = 'md_abandonedcart_report_monthly';
    const TBL_MYCUSTOMREPORT_YEARLY = 'md_abandonedcart_report_yearly';

    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('md_abandonedcart_email_black_list'),
                'website_id',
                [
                    'type'      =>  \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable'  => false,
                    'comment'   => 'Website ID'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('md_abandonedcart_email_queue'),
                'product_name',
                [
                    'type'      =>  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'size'      =>255,
                    'nullable'  => false,
                    'comment'   => 'Product Name'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('md_abandonedcart_email_queue'),
                'product_sku',
                [
                    'type'      =>  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'size'      =>255,
                    'nullable'  => false,
                    'comment'   => 'Product Sku'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('md_abandonedcart_email_queue'),
                'schedule_at',
                [
                    'type'      =>  \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'size'      =>null,
                    'nullable'  => false,
                    'default'   =>\Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                    'comment'   => 'Schedule Time'
                ]
            );

            $installer = $setup;
            $installer->startSetup();

            $table = $installer->getConnection()->newTable(
                $installer->getTable('md_abandonedcart_reports')
            )->addColumn(
                'report_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Reports unique Id'
            )->addColumn(
                'reference_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Rule table id'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'customer Id'
            )->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Quote Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Product Id'
            )->addColumn(
                'product_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'true'],
                'Product Name'
            )->addColumn(
                'product_sku',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'true'],
                'Product sku'
            )->addColumn(
                'price',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'true'],
                'quote price'
            )->addColumn(
                'product_qty',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'true'],
                'Product Qty'
            )->addColumn(
                'first_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'true'],
                'Abandoned customerName'
            )->addColumn(
                'last_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'true'],
                'Abandoned customerLastName'
            )->addColumn(
                'email',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'true'],
                'Abandoned email'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                100,
                ['nullable' => false],
                'Store Id'
            )->addColumn(
                'is_restored',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                4,
                ['default'=>0],
                'Is cart Restored?	'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                4,
                [],
                '1 => Sent,2 => Fail To Send, 3 => Order Was Placed,
                 4 => Cart was Deleted, 5 => Another Cart Was Created,
                 6 => Some Product Went Out Of Stock,7=> All Products Out Of Stock'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Creation Time'
            )->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Modification Time'
            );

            $installer->getConnection()->createTable($table);
            $installer->endSetup();

            $tablesToCreate = [
                'daily' => self::TBL_MYCUSTOMREPORT_DAILY,
                'monthly' => self::TBL_MYCUSTOMREPORT_MONTHLY,
                'yearly' => self::TBL_MYCUSTOMREPORT_YEARLY
            ];

            foreach ($tablesToCreate as $key => $tbl) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable($tbl)
                )->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )->addColumn(
                    'period',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    [],
                    'Period'
                )->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true],
                    'Store Id'
                )->addColumn(
                    'reference_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    100,
                    ['nullable' => false,'unsigned'=>true],
                    'Rule table id	'
                )->addColumn(
                    'customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    100,
                    ['nullable' => false,'unsigned'=>true],
                    'Customer id'
                )->addColumn(
                    'quote_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    100,
                    ['nullable' => false,'unsigned'=>true],
                    'Quote id'
                )->addColumn(
                    'first_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Abandoned customerName'
                )->addColumn(
                    'last_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Abandoned customerLastName'
                )->addColumn(
                    'email',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Abandoned email'
                )->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true],
                    'Product Id'
                )->addColumn(
                    'product_sku',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Product SKU'
                )->addColumn(
                    'product_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Product Name'
                )->addColumn(
                    'qty_ordered',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false, 'default' => '0.0000'],
                    'Qty Ordered'
                )->addIndex(
                    $installer->getIdxName(
                        $tbl,
                        ['period', 'store_id', 'product_id','quote_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['period', 'store_id', 'product_id','quote_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addIndex(
                    $installer->getIdxName($tbl, ['store_id']),
                    ['store_id']
                )->addIndex(
                    $installer->getIdxName($tbl, ['product_id']),
                    ['product_id']
                )->addIndex(
                    $installer->getIdxName($tbl, ['quote_id']),
                    ['quote_id']
                )->addForeignKey(
                    $installer->getFkName($tbl, 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->setComment(
                    'MyCustomReport Aggregated '.ucfirst($key)
                );

                $installer->getConnection()->createTable($table);
                $installer->endSetup();
            }
        }
        $uniqueColums = ['website_id', 'email'];

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
                $setup->getConnection()->addIndex(
                    $setup->getTable('md_abandonedcart_email_black_list'),
                    $setup->getIdxName(
                        'md_abandonedcart_email_black_list',
                        $uniqueColums,
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    $uniqueColums,
                    AdapterInterface::INDEX_TYPE_UNIQUE
                );
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $table = $setup->getTable('md_abandonedcart_rule');
            $setup->getConnection()
                ->addIndex(
                    $table,
                    $setup->getIdxName(
                        $table,
                        ['rule_name'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['rule_name'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                );
        }
        
        $setup->endSetup();
    }
}
