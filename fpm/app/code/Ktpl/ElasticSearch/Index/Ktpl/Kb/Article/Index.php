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

namespace Ktpl\ElasticSearch\Index\Ktpl\Kb\Article;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Ktpl\Kb\Article
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
        return 'Ktpl / Knowledge Base';
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'ktpl_kb_article';
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
            'text' => __('Content'),
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
        return 'article_id';
    }

    /**
     * Build Search Collection
     *
     * @return \Ktpl\Kb\Model\ResourceModel\Article\CollectionFactory|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection()
    {
        /** @var \Ktpl\Kb\Model\ResourceModel\Article\CollectionFactory $collection */
        $collectionFactory = ObjectManager::getInstance()
            ->create('Ktpl\Kb\Model\ResourceModel\Article\CollectionFactory');

        $collection = $collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.article_id');

        return $collection;
    }

    /**
     * Get Searchable Entities
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return array|\Ktpl\Kb\Model\ResourceModel\Article\CollectionFactory|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        /** @var \Ktpl\Kb\Model\ResourceModel\Article\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Ktpl\Kb\Model\ResourceModel\Article\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addStoreIdFilter($storeId)
            ->addFieldToFilter('main_table.is_active', 1);

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.article_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.article_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('main_table.article_id');

        return $collection;
    }
}
