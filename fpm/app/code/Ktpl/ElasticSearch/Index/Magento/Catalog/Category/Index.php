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

namespace Ktpl\ElasticSearch\Index\Magento\Catalog\Category;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Ktpl\ElasticSearch\Model\Index\AbstractIndex;
use Ktpl\ElasticSearch\Model\Index\Context;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Magento\Catalog\Category
 */
class Index extends AbstractIndex
{
    /**
     * @var CategoryCollectionFactory
     */
    protected $collectionFactory;

    /**
     * Index constructor.
     *
     * @param CategoryCollectionFactory $collectionFactory
     * @param Context $context
     * @param array $dataMappers
     */
    public function __construct(
        CategoryCollectionFactory $collectionFactory,
        Context $context,
        $dataMappers
    )
    {
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context, $dataMappers);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'Magento / Category';
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'magento_catalog_category';
    }

    /**
     * Get primary key
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return 'entity_id';
    }

    /**
     * Build Search Collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection()
    {
        $collection = $this->collectionFactory->create()
            ->addNameToResult()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('level', ['gt' => 1]);

        if (strpos($collection->getSelect(), '`e`') !== false) {
            $this->context->getSearcher()->joinMatches($collection, 'e.entity_id');
        } else {
            $this->context->getSearcher()->joinMatches($collection, 'main_table.entity_id');
        }

        return $collection;
    }

    /**
     * Get Searchable Entities
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return array|\Magento\Catalog\Model\ResourceModel\Category\Collection|\Magento\Framework\Data\Collection\AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->context->getStoreManager()->getStore($storeId);

        $root = $store->getRootCategoryId();

        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect(array_keys($this->getAttributes()))
            ->setStoreId($storeId)
            ->addPathsFilter("1/$root/")
            ->addFieldToFilter('is_active', 1);

        if ($entityIds) {
            $collection->addFieldToFilter('entity_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('entity_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('entity_id');

        foreach ($collection as $item) {
            $item->setData('description', $this->prepareHtml($item->getData('description'), $storeId));
            $item->setData('landing_page', $this->renderCmsBlock($item->getData('landing_page'), $storeId));
        }

        return $collection;
    }

    /**
     * Get attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            'name' => __('Name'),
            'description' => __('Description'),
            'meta_title' => __('Page Title'),
            'meta_keywords' => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
            'landing_page' => __('CMS Block'),
        ];
    }

    /**
     * Prepare HTML
     *
     * @param $html
     * @param $storeId
     * @return string
     */
    protected function prepareHtml($html, $storeId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Email\Model\TemplateFactory $emailTemplateFactory */
        $emailTemplateFactory = $objectManager->create('Magento\Email\Model\TemplateFactory');

        /** @var \Magento\Cms\Model\Template\FilterProvider $filterProvider */
        $filterProvider = $objectManager->create('Magento\Cms\Model\Template\FilterProvider');

        try {
            /** @var \Magento\Store\Model\App\Emulation $emulation */
            $emulation = $objectManager->create('Magento\Store\Model\App\Emulation');
            $emulation->startEnvironmentEmulation($storeId);

            $template = $emailTemplateFactory->create();
            $template->emulateDesign($storeId);
            $template->setTemplateText($html)
                ->setIsPlain(false);
            $template->setTemplateFilter($filterProvider->getPageFilter());
            $html = $template->getProcessedTemplate([]);

            $emulation->stopEnvironmentEmulation();
        } catch (\Exception $e) {
        }

        return $html;
    }

    /**
     * Render CMS block
     *
     * @param $blockId
     * @param $storeId
     * @return string
     */
    protected function renderCmsBlock($blockId, $storeId)
    {
        if ($blockId == 0) {
            return '';
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        try {
            /** @var \Magento\Cms\Api\BlockRepositoryInterface $blockRepository */
            $blockRepository = $objectManager->get('Magento\Cms\Api\BlockRepositoryInterface');

            $block = $blockRepository->getById($blockId);

            return $this->prepareHtml($block->getContent(), $storeId);
        } catch (\Exception $e) {
        }

        return '';
    }
}
