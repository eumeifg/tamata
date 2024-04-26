<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\Commissions\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogAttribute;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class InstallDataPatch implements
    DataPatchInterface,
    PatchRevertableInterface
{
    const MD_COMMISSION = 'md_commission';
    const MD_CALCULATION_TYPE = 'md_calculation_type';
    const MD_MARKETPLACE_FEE = 'md_marketplace_fee';
    const MD_MARKETPLACE_FEE_CALC_TYPE = 'md_marketplace_fee_calc_type';
    const MD_CANCELLATION_FEE = 'md_cancellation_fee';
    const MD_CANCELLATION_CALC_TYPE = 'md_cancellation_calc_type';

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    private $catalogSetupFactory;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->catalogSetupFactory = $categorySetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $catalogSetup = $this->catalogSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $catalogSetup->addAttribute(Product::ENTITY, self::MD_COMMISSION, [
            'label' => 'Commission',
            'group'             => 'General',
            'required'          => false,
            'visible_on_front'  => false,
            'visible'           => true,
            'type'              => 'varchar',
            'input'             => 'text',
            'apply_to'=>'simple',
            'global' => CatalogAttribute::SCOPE_STORE
        ]);

        $catalogSetup->addAttribute(Product::ENTITY, self::MD_CALCULATION_TYPE, [
            'label' => 'Calculation Type',
            'group'             => 'General',
            'required'          => false,
            'visible_on_front'  => false,
            'visible'           => true,
            'source' => \Magedelight\Commissions\Model\Source\CalculationType::class,
            'type'              => 'varchar',
            'input'             => 'select',
            'apply_to'=>'simple',
            'global' => CatalogAttribute::SCOPE_STORE
        ]);

        $catalogSetup->addAttribute(
            Product::ENTITY,
            self::MD_MARKETPLACE_FEE,
            [
            'label' => 'Marketplace Fee',
            'group' => 'General',
            'required' => false,
            'visible_on_front' => false,
            'visible' => true,
            'type' => 'varchar',
            'input' => 'text',
            'apply_to' => 'simple,virtual,downloadable',
            'global' => CatalogAttribute::SCOPE_STORE
            ]
        );

        $catalogSetup->addAttribute(
            Product::ENTITY,
            self::MD_MARKETPLACE_FEE_CALC_TYPE,
            [
            'label' => 'Marketplace Fee Type',
            'group' => 'General',
            'required' => false,
            'visible_on_front' => false,
            'visible' => true,
            'source' => \Magedelight\Commissions\Model\Source\CalculationType::class,
            'type' => 'varchar',
            'input' => 'select',
            'apply_to' => 'simple,virtual,downloadable',
            'global' => CatalogAttribute::SCOPE_STORE
            ]
        );

        $catalogSetup->addAttribute(
            Product::ENTITY,
            self::MD_CANCELLATION_FEE,
            [
            'label' => 'Cancellation Fee',
            'group' => 'General',
            'required' => false,
            'visible_on_front' => false,
            'visible' => true,
            'type' => 'varchar',
            'input' => 'text',
            'apply_to' => 'simple,virtual,downloadable',
            'global' => CatalogAttribute::SCOPE_STORE
            ]
        );

        $catalogSetup->addAttribute(
            Product::ENTITY,
            self::MD_CANCELLATION_CALC_TYPE,
            [
            'label' => 'Cancellation Fee Type',
            'group' => 'General',
            'required' => false,
            'visible_on_front' => false,
            'visible' => true,
            'source' => \Magedelight\Commissions\Model\Source\CalculationType::class,
            'type' => 'varchar',
            'input' => 'select',
            'apply_to' => 'simple,virtual,downloadable',
            'global' => CatalogAttribute::SCOPE_STORE
            ]
        );

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            self::MD_CALCULATION_TYPE,
            'is_global',
            CatalogAttribute::SCOPE_WEBSITE
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            self::MD_MARKETPLACE_FEE,
            'is_global',
            CatalogAttribute::SCOPE_WEBSITE
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            self::MD_MARKETPLACE_FEE_CALC_TYPE,
            'is_global',
            CatalogAttribute::SCOPE_WEBSITE
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            self::MD_CANCELLATION_FEE,
            'is_global',
            CatalogAttribute::SCOPE_WEBSITE
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            self::MD_CANCELLATION_CALC_TYPE,
            'is_global',
            CatalogAttribute::SCOPE_WEBSITE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    public function revert()
    {
        $catalogSetup = $this->catalogSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $catalogSetup->removeAttribute(Product::ENTITY, self::MD_COMMISSION);
        $catalogSetup->removeAttribute(Product::ENTITY, self::MD_CALCULATION_TYPE);
        $catalogSetup->removeAttribute(Product::ENTITY, self::MD_MARKETPLACE_FEE);
        $catalogSetup->removeAttribute(Product::ENTITY, self::MD_MARKETPLACE_FEE_CALC_TYPE);
        $catalogSetup->removeAttribute(Product::ENTITY, self::MD_CANCELLATION_FEE);
        $catalogSetup->removeAttribute(Product::ENTITY, self::MD_CANCELLATION_CALC_TYPE);
    }
}
