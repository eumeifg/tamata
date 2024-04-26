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

namespace Ktpl\ElasticSearch\Index\Blackbird\ContentManager\Content;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Blackbird\ContentManager\Content
 */
class Index extends AbstractIndex
{
    /**
     * Get name
     *
     * return string
     */
    public function getName()
    {
        return 'Blackbird / Content Manager';
    }

    /**
     * Get identifier
     *
     * return string
     */
    public function getIdentifier()
    {
        return 'blackbird_contentmanager_content';
    }

    /**
     * Get attributes
     *
     * return array
     */
    public function getAttributes()
    {
        $attributes = [
            'title' => __('Title'),
        ];

        return $attributes;
    }

    /**
     * Get primary key
     *
     * return string
     */
    public function getPrimaryKey()
    {
        return 'entity_id';
    }

    /**
     * Build Search Collection
     *
     * @return \Blackbird\ContentManager\Model\ResourceModel\Content\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection()
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory');

        /** @var \Blackbird\ContentManager\Model\ResourceModel\Content\Collection $collection */
        $collection = $collectionFactory->create();

        $collection
            ->addAttributeToFilter('status', 1)
            ->addAttributeToSelect('*');

        if (count($this->getSearchableTypes())) {
            $collection->addContentTypeFilter($this->getSearchableTypes());
        }

        $this->context->getSearcher()->joinMatches($collection, 'e.entity_id');

        return $collection;
    }

    /**
     * Get Searchable Types
     *
     * @return array
     */
    private function getSearchableTypes()
    {
        $types = $this->getModel()->getProperty('content_types');

        $types = is_array($types) ? array_filter($types) : [];

        return $types;
    }

    /**
     * Get Searchable Entities
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return array|\Blackbird\ContentManager\Model\ResourceModel\Content\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory');

        /** @var \Blackbird\ContentManager\Model\ResourceModel\Content\Collection $collection */
        $collection = $collectionFactory->create();

        $collection->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('entity_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('entity_id', ['gt' => $lastEntityId])
            ->addAttributeToSelect('*')
            ->setPageSize($limit)
            ->setOrder('entity_id');

        return $collection;
    }
}
