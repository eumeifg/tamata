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

namespace Ktpl\ElasticSearch\Index\Magefan\Blog\Post;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Magefan\Blog\Post
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
        return 'Magefan / Blog';
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'magefan_blog_post';
    }

    /**
     * Get attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            'title' => __('Title'),
            'content_heading' => __('Content Heading'),
            'content' => __('Content'),
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
        return 'post_id';
    }

    /**
     * Build Search Collection
     *
     * @return \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection()
    {
        /** @var \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Magefan\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addFieldToFilter('is_active', 1);

        $this->context->getSearcher()->joinMatches($collection, 'main_table.post_id');

        return $collection;
    }

    /**
     * Get Searchable Entities
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return array|\Magefan\Blog\Model\ResourceModel\Post\CollectionFactory|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        /** @var \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Magefan\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create();

        $collection->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('post_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('post_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('post_id');

        return $collection;
    }
}
