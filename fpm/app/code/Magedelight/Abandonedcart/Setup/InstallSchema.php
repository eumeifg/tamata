<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Setup;

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
            ->newTable($installer->getTable('md_abandonedcart_rule'))
            ->addColumn(
                'abandoned_cart_rule_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Abandoned Cart ID'
            )
            ->addColumn(
                'rule_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => 'true'],
                'Abandoned Cart Rule Name'
            )
            ->addColumn('status', Table::TYPE_SMALLINT, 4, ['nullable' => false, 'default' => '1'], 'Status')
            ->addColumn('priority', Table::TYPE_TEXT, 50, ['nullable' => true], 'Rule Priority')
            ->addColumn(
                'cancel_condition',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Cancel Condition Comaseperated'
            )
            ->addColumn('store_ids', Table::TYPE_TEXT, 50, ['nullable' => true], 'Store ids')
            ->addColumn(
                'customers_group_ids',
                Table::TYPE_TEXT,
                50,
                ['nullable' => true],
                'Rule apply on Customer Group Ids'
            )
            ->addColumn('conditions_serialized', Table::TYPE_TEXT, '64k', ['nullable' => true], 'Conditions for rule')
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
            )
            ->addIndex(
                $installer->getIdxName(
                    'abandoned_cart_rule_id',
                    ['abandoned_cart_rule_id']
                ),
                ['abandoned_cart_rule_id']
            )
            ->setComment('Abandoned Cart Rules');
        $installer->getConnection()->createTable($table);

        /*Create Abandoned Cart Schedule*/
        $scheduletable = $installer->getConnection()
            ->newTable($installer->getTable('md_abandonedcart_schedule'))
            ->addColumn(
                'schedule_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Abandoned Cart Rule Schedule Email ID'
            )
            ->addColumn(
                'abandoned_cart_rule_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Ambadoned Cart Rule Id'
            )
            ->addColumn('email_template_id', Table::TYPE_SMALLINT, null, ['nullable' => false], 'Email Template ID')
            ->addColumn('schedule_hours', Table::TYPE_SMALLINT, null, ['nullable' => true], 'Schedule Hours')
            ->addColumn('schedule_minute', Table::TYPE_SMALLINT, null, ['nullable' => true], 'Schedule Minute')
            ->addColumn('schedule_sec', Table::TYPE_SMALLINT, null, ['nullable' => false], 'Schedule Second')
            ->addColumn('send_coupon', Table::TYPE_SMALLINT, 4, ['nullable' => false, 'default' => 0], 'Send Coupon')
            ->addColumn(
                'cartprice_rule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Cart Price Rule ID'
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
            )
            ->addIndex(
                $installer->getIdxName(
                    'abandoned_cart_rule_id',
                    ['abandoned_cart_rule_id']
                ),
                ['abandoned_cart_rule_id']
            )
            ->addIndex($installer->getIdxName('schedule_id', ['schedule_id']), ['schedule_id'])
            ->addForeignKey(
                $installer->getFkName(
                    'md_abandonedcart_schedule',
                    'abandoned_cart_rule_id',
                    'md_abandonedcart_rule',
                    'abandoned_cart_rule_id'
                ),
                'abandoned_cart_rule_id',
                $installer->getTable('md_abandonedcart_rule'),
                'abandoned_cart_rule_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Abandoned Cart Rule Schedule');
        $installer->getConnection()->createTable($scheduletable);
        
        /*Create Abandoned Cart Schedule*/

        /*Create Abandoned Cart Email Queue*/
        $emailQueueTable = $installer->getConnection()
            ->newTable($installer->getTable('md_abandonedcart_email_queue'))
            ->addColumn(
                'abandonedcart_email_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Abandoned Cart Email Queue ID'
            )
            ->addColumn('first_name', Table::TYPE_TEXT, 255, ['nullable' => true], 'First Name')
            ->addColumn('last_name', Table::TYPE_TEXT, 255, ['nullable' => true], 'Last Name')
            ->addColumn('email', Table::TYPE_TEXT, 255, ['nullable' => false], 'Email')
            ->addColumn('customer_id', Table::TYPE_INTEGER, null, ['nullable' => true], 'Customer Id')
            ->addColumn('template_id', Table::TYPE_INTEGER, null, ['nullable' => true], 'Email Template ID')
            ->addColumn('variables', Table::TYPE_TEXT, '64k', ['nullable' => true], 'Email template variables data')
            ->addColumn('template_code', Table::TYPE_TEXT, 255, ['nullable' => false], 'Scheduled Template Code')
            ->addColumn('email_content', Table::TYPE_TEXT, '64k', ['nullable' => false], 'Email Content')
            ->addColumn('schedule_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'Schedule Id')
            ->addColumn('send_coupon', Table::TYPE_SMALLINT, 4, ['nullable' => true], 'Send Coupon')
            ->addColumn('cartprice_rule_id', Table::TYPE_INTEGER, null, ['nullable' => true], 'Cart Price Rule Id')
            ->addColumn('reference_id', Table::TYPE_TEXT, 255, ['nullable' => true], 'Reference Id')
            ->addColumn('quote_id', Table::TYPE_TEXT, 255, ['nullable' => false], 'Quote ID')
            ->addColumn('quote_item_id', Table::TYPE_TEXT, 255, ['nullable' => false], 'Quote Item ID')
            ->addColumn('product_id', Table::TYPE_TEXT, 255, ['nullable' => false], 'Product ID')
            ->addColumn('schedule_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false,
            'default' =>\Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'Schedule Time')
            ->addColumn('is_sent', Table::TYPE_SMALLINT, 4, ['nullable' => false, 'default' => 0], 'Is email Sent')
            ->addColumn('store_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => 0], 'store_id')
            ->addColumn('status', Table::TYPE_SMALLINT, 4, ['nullable' => false, 'default' => 1], '1 => Sent,
                2 => Fail To Send, 3 => Order Was Placed, 4 => Cart was Deleted,
                5 => Another Cart Was Created,6 => Some Product Went Out Of Stock,7=> All Products Out Of Stock')
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
            )
            ->addForeignKey(
                $installer->getFkName(
                    'md_abandonedcart_email_queue',
                    'schedule_id',
                    'md_abandonedcart_schedule',
                    'schedule_id'
                ),
                'schedule_id',
                $installer->getTable('md_abandonedcart_schedule'),
                'schedule_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addIndex($installer->getIdxName('schedule_id', ['schedule_id']), ['schedule_id'])
            ->addIndex(
                $installer->getIdxName('abandonedcart_email_id', ['abandonedcart_email_id']),
                ['abandonedcart_email_id']
            )
            ->addIndex($installer->getIdxName('reference_id', ['reference_id']), ['reference_id'])
            ->addIndex($installer->getIdxName('quote_id', ['quote_id']), ['quote_id'])
            ->addIndex($installer->getIdxName('send_coupon', ['send_coupon']), ['send_coupon'])
            ->addIndex($installer->getIdxName('cartprice_rule_id', ['cartprice_rule_id']), ['cartprice_rule_id'])
            ->setComment('Abandoned Cart Email Queue');
        $installer->getConnection()->createTable($emailQueueTable);

        $emailQueueTable = $installer->getConnection()
            ->newTable($installer->getTable('md_abandonedcart_history'))
            ->addColumn(
                'history_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Abandoned Cart History ID'
            )
            ->addColumn('first_name', Table::TYPE_TEXT, 255, ['nullable' => true], 'First Name')
            ->addColumn('last_name', Table::TYPE_TEXT, 255, ['nullable' => true], 'Last Name')
            ->addColumn('email', Table::TYPE_TEXT, 255, ['nullable' => false], 'Email')
            ->addColumn('customer_id', Table::TYPE_SMALLINT, 4, ['nullable' => true], 'Customer Id')
            ->addColumn('template_id', Table::TYPE_INTEGER, null, ['nullable' => true], 'Email Template ID')
            ->addColumn('variables', Table::TYPE_TEXT, '64k', ['nullable' => true], 'Email template variables data')
            ->addColumn('template_code', Table::TYPE_TEXT, 255, ['nullable' => false], 'Scheduled Template Code')
            ->addColumn('email_content', Table::TYPE_TEXT, '64k', ['nullable' => false], 'Email Content')
            ->addColumn('schedule_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'Schedule Id')
            ->addColumn('queue_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'Queue Id')
            ->addColumn('send_coupon', Table::TYPE_SMALLINT, 4, ['nullable' => true], 'Send Coupon')
            ->addColumn('cartprice_rule_id', Table::TYPE_INTEGER, null, ['nullable' => true], 'Cart Price Rule Id')
            ->addColumn('reference_id', Table::TYPE_TEXT, 255, ['nullable' => true], 'Reference Id')
            ->addColumn('quote_id', Table::TYPE_TEXT, 4, ['nullable' => false], 'Quote ID')
            ->addColumn('schedule_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'Schedule Time')
            ->addColumn('is_sent', Table::TYPE_SMALLINT, 4, ['nullable' => false, 'default' => 0], 'Is email Sent')
            ->addColumn('store_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => 0], 'store_id')
            ->addColumn(
                'is_restored',
                Table::TYPE_SMALLINT,
                4,
                ['nullable' => false, 'default' => 0],
                'Is cart Restored?'
            )
            ->addColumn('is_ordered', Table::TYPE_SMALLINT, 4, ['nullable' => false, 'default' => 0], 'Is Ordered?')
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                4,
                ['nullable' => false, 'default' => 1],
                '1 => Sent,2 => Fail To Send, 3 => Order Was Placed,
                 4 => Cart was Deleted, 5 => Another Cart Was Created,
                 6 => Some Product Went Out Of Stock,7=> All Products Out Of Stock'
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
            )
            ->addIndex($installer->getIdxName('history_id', ['history_id']), ['history_id'])
            ->addIndex($installer->getIdxName('reference_id', ['reference_id']), ['reference_id'])
            ->addIndex($installer->getIdxName('quote_id', ['quote_id']), ['quote_id'])
            ->addIndex($installer->getIdxName('is_sent', ['is_sent']), ['is_sent'])
            ->addIndex($installer->getIdxName('customer_id', ['customer_id']), ['customer_id'])
            ->addIndex($installer->getIdxName('status', ['status']), ['status'])
            ->addIndex($installer->getIdxName('cartprice_rule_id', ['cartprice_rule_id']), ['cartprice_rule_id'])
            ->setComment('Abandoned Cart History');
        $installer->getConnection()->createTable($emailQueueTable);
        /*Create Abandoned Cart Email Queue*/

        /*Create Abandoned Cart Email Black List*/
        $blackListTable = $installer->getConnection()
            ->newTable($installer->getTable('md_abandonedcart_email_black_list'))
            ->addColumn(
                'black_list_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Abandoned Cart Email Black List'
            )
            ->addColumn('email', Table::TYPE_TEXT, 255, ['nullable' => false], 'Email')
            ->addColumn(
                'creation_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addIndex($installer->getIdxName('email', ['email']), ['email'])
            ->setComment('Abandoned Cart Email Black Lsit');
            
        $installer->getConnection()->createTable($blackListTable);
        
        if (!$installer->tableExists('md_abandonedcart_email_black_list_stores')) {
            $blackListStoresTable = $installer->getConnection()
                ->newTable($installer->getTable('md_abandonedcart_email_black_list_stores'))
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Store ID'
                )
                ->addColumn(
                    'black_list_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false],
                    'Abandoned Cart Email Black List'
                )
                ->addIndex(
                    $installer->getIdxName('md_abandonedcart_email_black_list_stores', ['black_list_id']),
                    ['black_list_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'md_abandonedcart_email_black_list_stores',
                        'black_list_id',
                        'md_abandonedcart_email_black_list',
                        'black_list_id'
                    ),
                    'black_list_id',
                    'md_abandonedcart_email_black_list',
                    'black_list_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Blacklist Stores')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
                
            $installer->getConnection()->createTable($blackListStoresTable);
        }
        /* Create Abandoned Cart Email Black List ends here */
        
        $installer->endSetup();
    }
}
