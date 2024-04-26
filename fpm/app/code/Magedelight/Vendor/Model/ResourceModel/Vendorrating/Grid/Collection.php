<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid;

use Magedelight\Vendor\Model\ResourceModel\Vendorrating\Collection as VendorratingCollection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Collection extends VendorratingCollection implements SearchResultInterface
{
    /**
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param null|\Zend_Db_Adapter_Abstract $mainTable
     * @param string $eventPrefix
     * @param string $eventObject
     * @param string $resourceModel
     * @param string $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|string|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return $this|VendorratingCollection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getCustomerName();
        $this->getRating();
        return $this;
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    protected function getRating()
    {
        $collection = $this->getSelect()->joinInner(
            ['ocrb' => 'md_vendor_rating_rating_type'],
            "main_table.vendor_rating_id = ocrb.vendor_rating_id",
            [
                'ROUND(SUM(`ocrb`.`rating_avg`)) as rating_avg'

            ]
        );
        $this->getSelect()->group('main_table.vendor_rating_id');
        return $collection;
    }

    /**
     *
     */
    protected function getCustomerName()
    {
        $this->getSelect()->joinLeft(
            ['oce' => 'customer_entity'],
            "main_table.customer_id = oce.entity_id",
            [
                'CONCAT(oce.firstname," ", oce.lastname) as customer_name',
                'oce.firstname',
                'oce.lastname',
                'oce.email'
            ]
        );
    }

    /**
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return SearchResultInterface|void
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * @return \Magento\Framework\Api\Search\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return $this|SearchResultInterface
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * @param int $totalCount
     * @return $this|SearchResultInterface
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * @param array|null $items
     * @return $this|SearchResultInterface
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * @param $select1
     * @return mixed
     * @throws \Zend_Db_Statement_Exception
     */
    public function getAvarageSummaryForVendor($select1)
    {
        $select = "select avg(rating_avg) as  vendor_rating_avg from ($select1) as avgratings";

        $connection = $this->getConnection();

        $stmt = $connection->query($select);

        return $row = $stmt->fetch();
    }
}
