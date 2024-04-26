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

namespace Ktpl\ElasticSearch\Index\Ves\Brand\Brand;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Ves\Brand\Brand
 */
class Index extends AbstractIndex
{
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'Ves / Brand';
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'ves_brand_brand';
    }

    /**
     * Get attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            'name' => __('Title'),
            'description' => __('Content'),
            'page_title' => __('Page Title'),
            'meta_keywords' => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
        ];
    }

    /**
     * Get primary key
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return 'brand_id';
    }

    /**
     * Build Search Collection
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb|\Ves\Brand\Model\ResourceModel\Brand\CollectionFactory
     */
    public function buildSearchCollection()
    {
        /** @var \Ves\Brand\Model\ResourceModel\Brand\CollectionFactory $collection */
        $collectionFactory = ObjectManager::getInstance()
            ->create('Ves\Brand\Model\ResourceModel\Brand\CollectionFactory');

        $collection = $collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.brand_id');

        return $collection;
    }

    /**
     * Get Searchable Entities
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return array|\Magento\Framework\Data\Collection\AbstractDb|\Ves\Brand\Model\ResourceModel\Brand\CollectionFactory
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        /** @var \Ves\Brand\Model\ResourceModel\Brand\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Ves\Brand\Model\ResourceModel\Brand\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.brand_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.brand_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('brand_id');

        return $collection;
    }
}
