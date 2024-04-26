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

namespace Ktpl\ElasticSearch\Setup\UpgradeData;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData104
 *
 * @package Ktpl\ElasticSearch\Setup\UpgradeData
 */
class UpgradeData104 implements UpgradeDataInterface
{
    /**
     * @var ModuleList
     */
    private $moduleList;

    /**
     * @var BlockInterfaceFactory
     */
    private $blockFactory;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * UpgradeData104 constructor.
     *
     * @param ModuleList $moduleList
     * @param BlockInterfaceFactory $blockFactory
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        ModuleList $moduleList,
        BlockInterfaceFactory $blockFactory,
        BlockRepositoryInterface $blockRepository
    )
    {
        $this->moduleList = $moduleList;
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($this->moduleList->has('WeltPixel_CmsBlockScheduler')) {
            throw new \Exception('Please disable WeltPixel_CmsBlockScheduler before upgrade. And enable it after upgrade');
        }

        /** @var \Magento\Cms\Api\Data\BlockInterface $block */
        $block = $this->blockFactory->create();

        $block->setIdentifier('no-results')
            ->setTitle('Search: No Results Suggestions')
            ->setContent($this->getBlockContent('no_results'))
            ->setIsActive(true);

        try {
            $this->blockRepository->save($block);
        } catch (\Exception $e) {
        }
    }


    /**
     * Get block content
     *
     * @param string $name
     * @return string
     */
    private function getBlockContent($name)
    {
        return file_get_contents(dirname(__FILE__) . "/data/$name.html");
    }
}