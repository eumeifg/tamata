<?php
namespace Ktpl\GoogleMapAddress\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\DB\Ddl\Table;

class UpgradeData implements UpgradeDataInterface
{ 

    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
     public function __construct(EavSetupFactory $eavSetupFactory,
     CustomerSetupFactory $customerSetupFactory,
     SalesSetupFactory $salesSetupFactory
        )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }
	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.3', '<=')) {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerSetup->updateAttribute('customer_address', 'street', [
           'multiline_count'  =>  1,
       ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'street');

       $attribute->save();
   	}

        if (version_compare($context->getVersion(), '1.0.2', '<=')) {
        
            $salesInstaller = $this->salesSetupFactory->create(
                ['resourceName' => 'sales_setup', 'setup' => $setup]
                );

            $salesInstaller->addAttribute(
                'order',
                'pickup_name',
                [
                  'type' => Table::TYPE_TEXT,
                  'length' => '64k', 'nullable' => true,
                  'grid' => true
                ]
            );

        }

       $setup->endSetup();
	}
}