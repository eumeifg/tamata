<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\Plugin;

class AfterCategoryLoad
{
        /**
         * @var \Magento\Catalog\Api\Data\CategoryExtensionFactory
         */
    protected $categoryExtensionFactory;
    protected $setup;

    /**
     * @param \Magento\Catalog\Api\Data\CategoryExtensionFactory $categoryExtensionFactory
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    public function __construct(
        \Magento\Catalog\Api\Data\CategoryExtensionFactory $categoryExtensionFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup
    ) {
        $this->categoryExtensionFactory = $categoryExtensionFactory;
        $this->setup = $setup;
    }
}
