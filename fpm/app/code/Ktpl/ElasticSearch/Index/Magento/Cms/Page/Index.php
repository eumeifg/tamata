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

namespace Ktpl\ElasticSearch\Index\Magento\Cms\Page;

use Ktpl\ElasticSearch\Model\Index\AbstractIndex;
use Ktpl\ElasticSearch\Model\Index\Context;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;

/**
 * Class Index
 *
 * @package Ktpl\ElasticSearch\Index\Magento\Cms\Page
 */
class Index extends AbstractIndex
{
    /**
     * @var PageCollectionFactory
     */
    protected $collectionFactory;

    /**
     * Index constructor.
     *
     * @param PageCollectionFactory $collectionFactory
     * @param Context $context
     * @param array $dataMappers
     */
    public function __construct(
        Context $context,
        PageCollectionFactory $collectionFactory,
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
        return 'Magento / Cms Page';
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'magento_cms_page';
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
            'content_heading' => __('Content Heading'),
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
        return 'page_id';
    }

    /**
     * Build Search Collection
     *
     * @return \Magento\Cms\Model\ResourceModel\Page\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection()
    {
        $collection = $this->collectionFactory->create();
        $this->context->getSearcher()->joinMatches($collection, 'main_table.page_id');
        return $collection;
    }

    /**
     * Get Searchable Entities
     *
     * @param int $storeId
     * @param null $entityIds
     * @param null $lastEntityId
     * @param int $limit
     * @return array|\Magento\Cms\Model\ResourceModel\Page\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collection = $this->collectionFactory->create()
            ->addStoreFilter($storeId)
            ->addFieldToFilter('is_active', 1);

        $ignored = $this->getModel()->getProperty('ignored_pages');
        if (is_array($ignored) && count($ignored)) {
            $collection->addFieldToFilter('identifier', ['nin' => $ignored]);
        }

        if ($entityIds) {
            $collection->addFieldToFilter('page_id', $entityIds);
        }

        $collection
            ->addFieldToFilter('page_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('page_id', 'asc');

        return $collection;
    }
}
