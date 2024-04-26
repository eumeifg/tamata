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

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;

/**
 * Class InstallData
 *
 * @package Ktpl\ElasticSearch\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    protected $indexRepository;

    /**
     * InstallData constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository
    )
    {
        $this->indexRepository = $indexRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // create default index
        $productIndex = $this->indexRepository->create()
            ->setIdentifier('catalogsearch_fulltext')
            ->setTitle('Products')
            ->setIsActive(1)
            ->setPosition(1);
        $this->indexRepository->save($productIndex);

        $categoryIndex = $this->indexRepository->create()
            ->setIdentifier('magento_catalog_category')
            ->setTitle('Categories')
            ->setIsActive(0)
            ->setPosition(2)
            ->setAttributes([
                'name' => 10,
                'description' => 5,
                'meta_title' => 9,
                'meta_keywords' => 1,
                'meta_description' => 1,
            ]);
        $this->indexRepository->save($categoryIndex);

        $pageIndex = $this->indexRepository->create()
            ->setIdentifier('magento_cms_page')
            ->setTitle('Information')
            ->setIsActive(0)
            ->setPosition(3)
            ->setAttributes([
                'title' => 10,
                'content' => 5,
                'content_heading' => 9,
                'meta_keywords' => 1,
                'meta_description' => 1,
            ]);
        $this->indexRepository->save($pageIndex);

        $setup->endSetup();
    }
}
