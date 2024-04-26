<?php

namespace Ktpl\CategoryView\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        $attributeCode = 'block_layout';
        $categorySetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                $attributeCode,
                [
                    'type' => 'text',
                    'label' => 'Block Layout',
                    'input' => 'text',
                    'source' => '',
                    'required' => false,
                    'sort_order' => 70,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Display Settings',
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false
                ]
            );

            /** Add attribute to category group */
        $categorySetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'display-settings'),
            $attributeCode,
            70
        );
    }
}
