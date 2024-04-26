<?php
namespace Cminds\Coupon\Setup;

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
            ->newTable($installer->getTable('coupon_error_messages'))
            ->addColumn('id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'id')
            ->addColumn('rule_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true], 'rule id')
            ->addColumn('coupon_id', Table::TYPE_INTEGER, 10, ['nullable' => false, 'unsigned' => true], 'coupon id')
            ->addColumn('coupon_not_apply_rule', Table::TYPE_TEXT, 100, ['nullable' => false], 'Coupon code exist but do not apply to the rule conditions')
            ->addColumn('coupon_expired', Table::TYPE_TEXT, null, ['nullable' => false], 'Coupon code exist but is expired')
            ->addColumn('customer_not_belong_group', Table::TYPE_TEXT, null, ['nullable' => false], 'Customer doesn\'t belong to the assigned customer group')
            ->addColumn('coupon_used_multiple', Table::TYPE_TEXT, null, ['nullable' => false], 'Message when coupon was used more than it can be used')
            ->addColumn('coupon_used_multiple_customer_group', Table::TYPE_TEXT, null, ['nullable' => true], 'Message when coupon was used more than it can be used in customer group')
            ->addColumn('coupon_other_messages', Table::TYPE_TEXT, null, ['nullable' => true], 'Any other error messages applies')
            ->addForeignKey('FK_CUSTOMCOUPONS_MESSAGES', 'rule_id', $installer->getTable('salesrule'), 'rule_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)
            ->setComment('coupon errors');

        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addIndex(
            $installer->getTable('coupon_error_messages'),
            'rule_id',
            ['rule_id']
        );

        $table = $installer->getConnection()
            ->newTable($installer->getTable('coupon_error_messages_count'))
            ->addColumn('id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'id')
            ->addColumn('rule_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'rule id')
            ->addColumn('coupon_id', Table::TYPE_INTEGER, 10, ['nullable' => false, 'unsigned' => true], 'coupon id')
            ->addColumn('coupon_not_apply_rule', Table::TYPE_INTEGER, 100, ['nullable' => false], 'Coupon code exist but do not apply to the rule conditions')
            ->addColumn('coupon_expired', Table::TYPE_INTEGER, null, ['nullable' => false], 'Coupon code exist but is expired')
            ->addColumn('customer_not_belong_group', Table::TYPE_INTEGER, null, ['nullable' => false], 'Customer doesn\'t belong to the assigned customer group')
            ->addColumn('coupon_used_multiple', Table::TYPE_INTEGER, null, ['nullable' => false], 'Message when coupon was used more than it can be used')
            ->addColumn('coupon_used_multiple_customer_group', Table::TYPE_INTEGER, null, ['nullable' => true], 'Message when coupon was used more than it can be used in customer group')
            ->addColumn('coupon_other_messages', Table::TYPE_INTEGER, null, ['nullable' => true], 'Any other error messages applies')
            ->addColumn('last_occured', Table::TYPE_DATETIME, null, ['nullable' => true], 'Last occured')
            ->addForeignKey('FK_CUSTOMCOUPONS_MESSAGES_COUNT', 'coupon_id', $installer->getTable('salesrule_coupon'), 'coupon_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)
            ->setComment('coupon errors count');

        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addIndex(
            $installer->getTable('coupon_error_messages_count'),
            'rule_id',
            ['rule_id']
        );

        $installer->getConnection()->addIndex(
            $installer->getTable('coupon_error_messages_count'),
            'coupon_id',
            ['coupon_id']
        );

        $table = $installer->getConnection()
            ->newTable($installer->getTable('coupon_error_messages_log'))
            ->addColumn('id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'id')
            ->addColumn('coupon_id', Table::TYPE_INTEGER, 10, ['nullable' => false, 'unsigned' => true], 'coupon id')
            ->addColumn('error_type', Table::TYPE_TEXT, null, ['nullable' => true], 'Error type')
            ->addColumn('datetime', Table::TYPE_DATETIME, null, ['nullable' => true], 'Datetime')
            ->addForeignKey('FK_CUSTOMCOUPONS_MESSAGES_LOG', 'coupon_id', $installer->getTable('salesrule_coupon'), 'coupon_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)
            ->setComment('coupon errors log');

        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addIndex(
            $installer->getTable('coupon_error_messages_log'),
            'coupon_id',
            ['coupon_id']
        );

        $installer->endSetup();
    }
}