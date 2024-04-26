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

namespace Ktpl\ElasticSearch\Index\Ktpl\Blog\Post;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Ktpl\Blog\Post
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
        return 'Ktpl / Blog MX';
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'ktpl_blog_post';
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
     * @return \Ktpl\Blog\Model\ResourceModel\Post\CollectionFactory|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection()
    {
        /** @var \Ktpl\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = ObjectManager::getInstance()
            ->create('Ktpl\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addVisibilityFilter();

        $this->context->getSearcher()->joinMatches($collection, 'e.entity_id');

        return $collection;
    }

    /**
     * Get Searchable Entities
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return array|\Ktpl\Blog\Model\ResourceModel\Post\CollectionFactory|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        /** @var \Ktpl\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Ktpl\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addAttributeToSelect(array_keys($this->getAttributes()))
            ->addVisibilityFilter();

        if ($entityIds) {
            $collection->addFieldToFilter('entity_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('entity_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('entity_id');

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
            'content' => __('Content'),
            'short_content' => __('Short Content'),
            'meta_title' => __('Meta Title'),
            'meta_keywords' => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
        ];
    }
}
