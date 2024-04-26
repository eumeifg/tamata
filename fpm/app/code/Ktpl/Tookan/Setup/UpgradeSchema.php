<?php

namespace Ktpl\Tookan\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;


class UpgradeSchema implements  UpgradeSchemaInterface
{
  public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            if ($setup->tableExists('md_vendor_order_status_log')) {
                $connection->dropTable($setup->getTable('md_vendor_order_status_log'));
            }

            $table = $connection->newTable($setup->getTable('md_vendor_order_status_log'))
                ->addColumn('id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Incremental ID')
                //->addColumn('order_id', Table::TYPE_TEXT, 255, [], 'Main Order ID')
                ->addColumn('shipment_id', Table::TYPE_TEXT, 255, [], 'Order shipment ID')
                ->addColumn('shipped_item_ids', Table::TYPE_TEXT, 255, [], 'Shipped Item ids')
                //->addColumn('vendor_id', Table::TYPE_TEXT, 255, [], 'Vendor ID')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => true, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'Order completed date');
            $setup->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}
