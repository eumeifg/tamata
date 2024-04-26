<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>.
 *
 * NOTICE OF LICENSE
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_SocialLogin
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */





namespace Magedelight\SocialLogin\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables.
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface   $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    // @codingStandardsIgnoreStart
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
    // @codingStandardsIgnoreEnd
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('magedelight_sociallogin')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('magedelight_sociallogin')
            )
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Id'
                )
                ->addColumn(
                    'social_id',
                    Table::TYPE_TEXT,
                    255,
                    ['unsigned' => true],
                    'Social Id'
                )
                ->addColumn(
                    'user_email',
                    Table::TYPE_TEXT,
                    255,
                    ['unsigned' => true],
                    'User Email'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true],
                    'Customer Id'
                )
                ->addColumn(
                    'is_send_password_email',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'unsigned' => true,
                        'default' => '0',
                    ],
                    'Is Send Password Email'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => ''],
                    'Type'
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'magedelight_sociallogin',
                        'customer_id',
                        'customer_entity',
                        'entity_id'
                    ),
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName('magedelight_sociallogin', ['id']),
                    ['id']
                )
                ->setComment('Magedelight Social Login');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
