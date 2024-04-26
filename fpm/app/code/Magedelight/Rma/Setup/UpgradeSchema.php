<?php
/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */
/**
 * @author Rocket Bazaar Core Team
 *  Created at 8 Mar, 2016 12:18:57 PM
 */

namespace Magedelight\Rma\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /* add vendor id into magento_rma table */
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('magento_rma'),
                'vendor_id',
                [
                    'type'     => Table::TYPE_BIGINT,
                    'nullable' => true,
                    'default'  => 0,
                    'comment'  => 'Vendor Id'
                ]
            );
            
            $setup->getConnection()->addColumn(
                $setup->getTable('magento_rma'),
                'vendor_name',
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment'  => 'Vendor Name'
                ]
            );
            
            $setup->getConnection()->addColumn(
                $setup->getTable('magento_rma_grid'),
                'vendor_id',
                [
                    'type'     => Table::TYPE_BIGINT,
                    'nullable' => true,
                    'default'  => 0,
                    'comment'  => 'Vendor Id'
                ]
            );
            
            $setup->getConnection()->addColumn(
                $setup->getTable('magento_rma_grid'),
                'vendor_name',
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment'  => 'Vendor Name'
                ]
            );
        }
        
        
        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('magento_rma_status_history'),
                'vendor_id',
                [
                    'type'     => Table::TYPE_BIGINT,
                    'nullable' => true,
                    'default'  => 0,
                    'comment'  => 'Vendor Id'
                ]
            );
            
            $setup->getConnection()->addColumn(
                $setup->getTable('magento_rma_status_history'),
                'vendor_name',
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment'  => 'Vendor Name'
                ]
            );
        }
        $setup->endSetup();
    }
}
