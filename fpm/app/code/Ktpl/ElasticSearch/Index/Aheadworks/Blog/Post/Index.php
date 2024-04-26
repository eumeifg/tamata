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

namespace Ktpl\ElasticSearch\Index\Aheadworks\Blog\Post;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Aheadworks\Blog\Post
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
        return 'Aheadworks / Blog';
    }

    /**
     * Get identifier
     *
     * return string
     */
    public function getIdentifier()
    {
        return 'aheadworks_blog_post';
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
            'short_content' => __('Content Heading'),
            'content' => __('Content'),
            'meta_title' => __('Meta Title'),
            'meta_description' => __('Meta Description'),
            'tag_names' => __('Tags'),
        ];
    }

    /**
     * Get primary key
     *
     * return string
     */
    public function getPrimaryKey()
    {
        return 'id';
    }

    /**
     * Buid search collection
     *
     * @return \Aheadworks\Blog\Model\ResourceModel\Post\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection()
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Aheadworks\Blog\Model\ResourceModel\Post\CollectionFactory');

        /** @var \Aheadworks\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $collectionFactory->create()
            ->addFieldToFilter('status', 'publication');

        $this->context->getSearcher()->joinMatches($collection, 'main_table.id');

        return $collection;
    }

    /**
     * Get searchable entities
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return \Aheadworks\Blog\Model\ResourceModel\Post\Collection|array|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Aheadworks\Blog\Model\ResourceModel\Post\CollectionFactory');

        /** @var \Aheadworks\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $collectionFactory->create();

        $collection->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('id');

        return $collection;
    }
}
