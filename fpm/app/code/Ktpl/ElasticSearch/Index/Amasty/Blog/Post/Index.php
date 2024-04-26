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

namespace Ktpl\ElasticSearch\Index\Amasty\Blog\Post;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Amasty\Blog\Post
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
        return 'Amasty / Blog';
    }

    /**
     * Get identifier
     *
     * return string
     */
    public function getIdentifier()
    {
        return 'amasty_blog_post';
    }

    /**
     * Get attributes
     *
     * return array
     */
    public function getAttributes()
    {
        return [
            'title' => __('Title'),
            'short_content' => __('Short Content'),
            'full_content' => __('Full Content'),
        ];
    }

    /**
     * Get primary key
     *
     * return string
     */
    public function getPrimaryKey()
    {
        return 'post_id';
    }

    /**
     * Build search collection
     *
     * @return \Amasty\Blog\Model\ResourceModel\Posts\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection()
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Amasty\Blog\Model\ResourceModel\Posts\CollectionFactory');

        /** @var \Amasty\Blog\Model\ResourceModel\Posts\Collection $collection */
        $collection = $collectionFactory->create()
            ->addFieldToFilter('status', 2);

        $this->context->getSearcher()->joinMatches($collection, 'main_table.post_id');

        return $collection;
    }

    /**
     * Get searchable entities
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return \Amasty\Blog\Model\ResourceModel\Posts\Collection|array|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Amasty\Blog\Model\ResourceModel\Posts\CollectionFactory');

        /** @var \Amasty\Blog\Model\ResourceModel\Posts\Collection $collection */
        $collection = $collectionFactory->create();

        $collection->addStoreFilter([0, $storeId]);

        if ($entityIds) {
            $collection->addFieldToFilter('post_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.post_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('post_id');

        return $collection;
    }
}
