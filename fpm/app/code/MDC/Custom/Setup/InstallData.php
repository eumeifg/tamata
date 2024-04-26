<?php
/**
 * A Magento 2 module named MDC/Custom
 * Copyright (C) 2019 
 * 
 * This file included in MDC/Custom is licensed under OSL 3.0
 * 
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace MDC\Custom\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;

class InstallData implements InstallDataInterface
{

    private $eavSetupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'is_marketing',
            [
                'type' => 'int',
                'label' => 'Is Marketing Category',
                'input' => 'boolean',
                'sort_order' => 60,
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'global' => 0,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'group' => 'General Information'
            ]
        );

        // $eavSetup->addAttribute(
        //     \Magento\Catalog\Model\Category::ENTITY,
        //     'mobile_category_banner',
        //     [
        //         'type' => 'varchar',
        //         'label' => 'Mobile Banner',
        //         'input' => 'image',
        //         'sort_order' => 333,
        //         'source' => '',
        //         'global' => 0,
        //         'visible' => true,
        //         'required' => false,
        //         'user_defined' => false,
        //         'default' => null,
        //         'group' => 'General Information',
        //         'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image'
        //     ]
        // );

         $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'mobile_category_banner',
            [
                'type' => 'varchar',
                'label' => 'Mobile Banner',
                'input' => 'image',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'visible_on_front' => true,
                'group' => 'General Information',
                'user_defined' => true,
                'backend' => \Magento\Catalog\Model\Category\Attribute\Backend\Image::class,
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'mobile_category_image',
            [
                'type' => 'varchar',
                'label' => 'Mobile Image',
                'input' => 'image',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'group' => 'General Information',
                'backend'       => \Magento\Catalog\Model\Category\Attribute\Backend\Image::class,
            ]
        );
    }
}
