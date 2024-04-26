<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 *
 * @package Ktpl\ElasticSearch\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var UpgradeDataInterface[]
     */
    private $pool;

    /**
     * UpgradeData constructor.
     *
     * @param UpgradeData\UpgradeData103 $upgrade103
     * @param UpgradeData\UpgradeData104 $upgrade104
     * @param UpgradeData\UpgradeData107 $upgrade107
     */
    public function __construct(
        UpgradeData\UpgradeData103 $upgrade103,
        UpgradeData\UpgradeData104 $upgrade104,
        UpgradeData\UpgradeData107 $upgrade107
    )
    {
        $this->pool = [
            '1.0.3' => $upgrade103,
            '1.0.4' => $upgrade104,
            '1.0.7' => $upgrade107,
        ];
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        foreach ($this->pool as $version => $upgrade) {
            if (version_compare($context->getVersion(), $version) < 0) {
                $upgrade->upgrade($setup, $context);
            }
        }
        $setup->endSetup();
    }
}
