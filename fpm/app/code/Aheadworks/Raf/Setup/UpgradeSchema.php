<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Setup;

use Aheadworks\Raf\Setup\Updater\Schema;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Aheadworks\Raf\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Schema
     */
    private $updater;

    /**
     * @param Schema $updater
     */
    public function __construct(
        Schema $updater
    ) {
        $this->updater = $updater;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->updater->update101($setup);
        }
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->updater->update110($setup);
        }

        $setup->endSetup();
    }
}
