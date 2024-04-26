<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Aheadworks\Raf\Setup\Updater\Data;

/**
 * Class UpgradeData
 *
 * @package Aheadworks\Raf\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Data
     */
    private $updater;

    /**
     * @param Data $updater
     */
    public function __construct(
        Data $updater
    ) {
        $this->updater = $updater;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->updater->update110($setup);
        }

        $setup->endSetup();
    }
}
