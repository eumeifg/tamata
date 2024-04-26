<?php

namespace CAT\Custom\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class CreateNewProductAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return MicroSitePositionAttribute|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        /* men_clothing_size */
        $eavSetup->addAttribute(
            'catalog_product', 'men_clothing_size', [
                'attribute_set_id' => 'Default',
                'type' => 'int',
                'label' => __('Men Clothing Size'),
                'input' => 'select',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => '',
                'backend_model' => '',
                'default' => 0,
                'visible' => true,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'required' => false,
                'group' => 'General',
                'sort_order' => 19,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'option' =>[
                    'values' => [
                        '29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','46','48','S','M','L','XS','XL','XXL','3XL','4XL','5XL','6XL','7XL','8XL','9XL','Free size'
                    ]
                ]
            ]
        );
        /* women_clothing_size */
        $eavSetup->addAttribute(
            'catalog_product', 'women_clothing_size', [
                'attribute_set_id' => 'Default',
                'type' => 'int',
                'label' => __('Women Clothing Size'),
                'input' => 'select',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => '',
                'backend_model' => '',
                'default' => 0,
                'visible' => true,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'required' => false,
                'group' => 'General',
                'sort_order' => 19,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'option' =>[
                    'values' => [
                        '25','26','27','28','29','30','31','32','33','34','36','37','38','39','40','41','42','43','44','46','48','50','52','54','56','S','M','L','XS','XL','XXL','3XL','4XL','5XL','6XL','7XL','8XL','9XL','Free size'
                    ]
                ]
            ]
        );
        /* baby_clothing_size */
        $eavSetup->addAttribute(
            'catalog_product', 'baby_clothing_size', [
                'attribute_set_id' => 'Default',
                'type' => 'int',
                'label' => __('Baby Clothing Size'),
                'input' => 'select',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => '',
                'backend_model' => '',
                'default' => 0,
                'visible' => true,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'required' => false,
                'group' => 'General',
                'sort_order' => 19,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'option' =>[
                    'values' => [
                        '14','0m','10m','10y','11m','11y','12m','12y','13m','13y','14m','14y','15m','15y','16m','16y','17m','17y','18m','18y','19m','1m','1y','20m','21m','22m','23m','24m','2m','2y','30m','3m','3y','4m','4y','5m','5y','6m','6y','7m','7y','8m','8y','9m','9y','Free size'
                    ]
                ]
            ]
        );
        /* underwear_size */
        $eavSetup->addAttribute(
            'catalog_product', 'underwear_size', [
                'attribute_set_id' => 'Default',
                'type' => 'int',
                'label' => __('Underwear Size'),
                'input' => 'select',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => '',
                'backend_model' => '',
                'default' => 0,
                'visible' => true,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'required' => false,
                'group' => 'General',
                'sort_order' => 19,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'option' =>[
                    'values' => [
                        '75','80','85','90','95','100','105','110'
                    ]
                ]
            ]
        );
        /* jewellery_sizes */
        $eavSetup->addAttribute(
            'catalog_product', 'jewellery_sizes', [
                'attribute_set_id' => 'Default',
                'type' => 'int',
                'label' => __('Jewellery Size'),
                'input' => 'select',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => '',
                'backend_model' => '',
                'default' => 0,
                'visible' => true,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'required' => false,
                'group' => 'General',
                'sort_order' => 19,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'option' =>[
                    'values' => [
                        '10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28'
                    ]
                ]
            ]
        );
        /* men_shoe */
        $eavSetup->addAttribute(
            'catalog_product', 'men_shoe', [
                'attribute_set_id' => 'Default',
                'type' => 'int',
                'label' => __('Men Shoe Size'),
                'input' => 'select',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => '',
                'backend_model' => '',
                'default' => 0,
                'visible' => true,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'required' => false,
                'group' => 'General',
                'sort_order' => 19,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'option' =>[
                    'values' => [
                        '40','40.5','40.67','41','41.33','41.5','42','42.5','42.67','43','43.33','43.5','44','44.5','45','45.33','45.5','46','46.5','46.67','47','47.33','48','48.67'
                    ]
                ]
            ]
        );
        /* women_shoe */
        $eavSetup->addAttribute(
            'catalog_product', 'women_shoe', [
                'attribute_set_id' => 'Default',
                'type' => 'int',
                'label' => __('Women Shoe'),
                'input' => 'select',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => '',
                'backend_model' => '',
                'default' => 0,
                'visible' => true,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'required' => false,
                'group' => 'General',
                'sort_order' => 19,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'option' =>[
                    'values' => [
                        '35','35.5','36','36.5','36.67','37','37.33','37.5','38','38.5','38.67','39','39.33','39.5','40','40.5','40.67','41','42','42.5','43','43.5','44','45','46','48'
                    ]
                ]
            ]
        );
        /* kids_shoe */
        $eavSetup->addAttribute(
            'catalog_product', 'kids_shoe', [
                'attribute_set_id' => 'Default',
                'type' => 'int',
                'label' => __('Kids Shoe'),
                'input' => 'select',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => '',
                'backend_model' => '',
                'default' => 0,
                'visible' => true,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'required' => false,
                'group' => 'General',
                'sort_order' => 19,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'option' =>[
                    'values' => [
                        '17','18','19','20','21','21.5','22','22.5','23','24','25','26','26.5','27','27.5','28','28.5','29','29.5','30','31','32','33','33.5','34','35','35.5','12-18m','17m','18-24m','18m','19m','3-6m','6-9m','9-12m'
                    ]
                ]
            ]
        );
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
