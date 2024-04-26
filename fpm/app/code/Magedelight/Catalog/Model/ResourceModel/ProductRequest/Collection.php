<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\ResourceModel\ProductRequest;

use Magento\Store\Model\StoreManagerInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'product_request_id';
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'vendor_prod_req_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'vendor_prod_req_collection';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Magedelight\Catalog\Model\ProductRequest::class,
            \Magedelight\Catalog\Model\ResourceModel\ProductRequest::class
        );
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }

    /**
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _toOptionArray($valueField = 'product_request_id', $labelField = 'name', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    /**
     * @param null $limit
     * @param null $offset
     * @return \Magento\Framework\DB\Select
     */
    protected function getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        $idsSelect->limit($limit, $offset);
        return $idsSelect;
    }

    /**
     * @param $collection
     * @param null $websiteId
     * @param array $columns
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addWebsiteData($collection, $websiteId = null, $columns = ['*'])
    {

        $websiteId = $websiteId ?? $this->storeManager->getStore()->getWebsiteId();
        $collection->getSelect()->join(
            ['mdvprw' => 'md_vendor_product_request_website'],
            'mdvprw.product_request_id = main_table.product_request_id AND mdvprw.website_id = '.$websiteId,
            $columns
        );
    }

    public function addStoreData($collection, $storeId = null)
    {
        $storeId = $storeId ?? $this->storeManager->getStore()->getId();
        $collection->getSelect()->join(
            ['mdvprs' => 'md_vendor_product_request_store'],
            'mdvprs.product_request_id = main_table.product_request_id AND mdvprs.store_id = '.$storeId,
            ['name']
        );
    }
}
