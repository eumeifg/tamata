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

namespace Ktpl\ElasticSearch\Index\Ves\Blog\Post;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Ves\Blog\Post
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
        return 'Ves / Blog';
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'ves_blog_post';
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
            'content' => __('Content'),
            'short_content' => __('Short Content'),
            'page_title' => __('Page Title'),
            'page_keywords' => __('Page Keywords'),
            'page_description' => __('Page Description'),
            'tags' => __('Tags'),
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
     * @return \Magento\Framework\Data\Collection\AbstractDb|\Ves\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    public function buildSearchCollection()
    {
        /** @var \Ves\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = ObjectManager::getInstance()
            ->create('Ves\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create();

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
     * @return array|\Magento\Framework\Data\Collection\AbstractDb|\Ves\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        /** @var \Ves\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Ves\Blog\Model\ResourceModel\Post\CollectionFactory');

        $storeManager = $this->context->getObjectManager()
            ->create('Magento\Store\Model\Store');

        $collection = $collectionFactory->create()
            ->addStoreFilter($storeManager->load($storeId));

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.post_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.post_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('post_id');

        return $collection;
    }
}
