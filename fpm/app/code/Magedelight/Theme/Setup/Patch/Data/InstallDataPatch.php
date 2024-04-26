<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\Theme\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class InstallDataPatch implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * Page factory
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var CategorySetupFactory
     */
      private $categorySetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        PageFactory $pageFactory,
        BlockFactory $blockFactory,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        /* Menu script starts. */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        
        $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'md_category_attribute_set_id', [
            'type' => 'int',
            'label' => 'Attribute Set',
            'input' => 'select',
            'sort_order' => 31,
            'source' => \Magedelight\Theme\Model\Source\SuggestedSets::class,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'group' => 'General Information',
            'user_defined' => 1,
            'default' => '0',
        ]);
        /* Menu script ends. */
        
        $this->moduleDataSetup->endSetup();
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
        $this->moduleDataSetup->startSetup();
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $categorySetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'md_category_attribute_set_id');
        $this->moduleDataSetup->endSetup();
    }
}
