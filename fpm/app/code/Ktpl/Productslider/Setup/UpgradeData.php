<?php 

namespace Ktpl\Productslider\Setup; 

use Magento\Framework\Setup\UpgradeDataInterface; 
use Magento\Framework\Setup\ModuleContextInterface; 
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Product;

class UpgradeData implements UpgradeDataInterface
{ 

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
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    { 
        $installer = $setup;
        $installer->startSetup();
        
        if (version_compare($context->getVersion(), '2.0.0', '<=')) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->updateAttribute(Product::ENTITY, 'is_featured', 
                'source_model', 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            );
        $installer->endSetup();
        }
    }
}