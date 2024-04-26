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

namespace Ktpl\ElasticSearch\Index\Mageplaza\Blog\Post;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Mageplaza\Blog\Post
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
        return 'Mageplaza / Blog';
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'mageplaza_blog_post';
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
            'short_description' => __('Short Description'),
            'post_content' => __('Content'),
            'meta_title' => __('Meta Title'),
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
     * @return \Magento\Framework\Data\Collection\AbstractDb|\Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    public function buildSearchCollection()
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addFieldToFilter('enabled', 1);

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
     * @return array|\Magento\Framework\Data\Collection\AbstractDb|\Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create();

        if ($entityIds) {
            $collection->addFieldToFilter('post_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('post_id', ['gt' => $lastEntityId])
            ->setPageSize($limit);

        $collection->getSelect()->order('post_id');

        return $collection;
    }
}
