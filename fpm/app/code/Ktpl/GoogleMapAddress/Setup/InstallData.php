<?php


namespace Ktpl\GoogleMapAddress\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{

    /**
     * Constructor
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        Config $eavConfig,
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavConfig            = $eavConfig;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute('customer_address', 'latitude', [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Latitude',
            'visible' => true,
            'required' => true,
            'user_defined' => true,
            'system'=> false,
            'group'=> 'General',
            'global' => true,
            'visible_on_front' => true,
            'sort_order' => 200
        ]);

        $eavSetup->addAttribute('customer_address', 'longitude', [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Longitude',
            'visible' => true,
            'required' => true,
            'user_defined' => true,
            'system'=> false,
            'group'=> 'General',
            'global' => true,
            'visible_on_front' => true,
            'sort_order' => 201
        ]);

        $eavSetup->addAttribute('customer_address', 'addresstype', [
            'type' => 'int',
            'input' => 'select',
            'label' => 'Address Type',
            'visible' => true,
            'required' => true,
            'user_defined' => true,
            'system'=> false,
            'group'=> 'General',
            'global' => true,
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
            'option' => [
                'values' =>
                    ['1' =>'Home', '2'=>'Office', '3'=>'Other']
            ],
            'visible_on_front' => true,
            'sort_order' => 199
        ]);

        $latitude = $this->eavConfig->getAttribute('customer_address', 'latitude');

        $latitude->setData(
            'used_in_forms',
            ['adminhtml_customer_address','customer_address_edit','customer_register_address']
        )->setData('sort_order',200);
        $latitude->save();

        $longitude = $this->eavConfig->getAttribute('customer_address', 'longitude');

        $longitude->setData(
            'used_in_forms',
            ['adminhtml_customer_address','customer_address_edit','customer_register_address']
        )->setData('sort_order',201);
        $longitude->save();

        $addressTypes = $this->eavConfig->getAttribute('customer_address', 'addresstype');

        $addressTypes->setData(
            'used_in_forms',
            ['adminhtml_customer_address','customer_address_edit','customer_register_address']
        )->setData('sort_order',199);
        $addressTypes->save();

        /**
         * A work around to add columns in table. This columns should be created automatically but are not being created
         * when address attribute is created using script.
         */
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $orderAddressTable = $installer->getTable('magento_customercustomattributes_sales_flat_order_address');

        $orderColumns = [
            'latitude' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Latitude',
            ],
            'longitude' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Longitude',
            ],
            'addresstype' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Address Type',
            ]
        ];

        foreach ($orderColumns as $name => $definition) {
            $connection->addColumn($orderAddressTable, $name, $definition);
        }


        $quoteAddressTable = $installer->getTable('magento_customercustomattributes_sales_flat_quote_address');

        $quoteColumns = [
            'latitude' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Latitude',
            ],
            'longitude' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Longitude',
            ],
            'addresstype' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Address Type',
            ]
        ];


        foreach ($quoteColumns as $name => $definition) {
            $connection->addColumn($quoteAddressTable, $name, $definition);
        }

        $installer->endSetup();
    }
}
