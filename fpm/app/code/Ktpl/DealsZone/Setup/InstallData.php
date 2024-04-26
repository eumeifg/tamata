<?php
/**
 * Ktpl
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_DealsZone
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com/)
 * @license     https://https://www.KrishTechnolabs.com/
 */

namespace Ktpl\DealsZone\Setup;

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
        $dealAttributeGroup = $categorySetup->addAttributeGroup($entityTypeId, $attributeSetId, 'deal_zone', '1000');
        /** Add attribute to category */
        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'is_deal_zone',
            [
                'type' => 'int',
                'label' => 'Is Deal Zone',
                'input' => 'boolean',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'required' => false,
                'sort_order' => 60,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Deal Zone Settings'
            ]
        );
        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'discount_label',
            [
                'type' => 'text',
                'label' => 'Discount Label',
                'input' => 'textarea',
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'required' => false,
                'sort_order' => 2,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Deal Zone Settings',
                'default' => ''
            ]
        );
        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'dealzone_image',
            [
                'type' => 'varchar',
                'label' => 'Image',
                'input' => 'image',
                'backend' => \Magento\Catalog\Model\Category\Attribute\Backend\Image::class,
                'required' => false,
                'sort_order' => 5,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Deal Zone Settings',
            ]
        );
        $id = $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'deal_zone');
        $categorySetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $id,
            'is_deal_zone',
            30
        );
        $categorySetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $id,
            'discount_label',
            31
        );
        $categorySetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $id,
            'dealzone_image',
            32
        );
    }
}
