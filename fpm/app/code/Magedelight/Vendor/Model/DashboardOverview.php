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
namespace Magedelight\Vendor\Model;

use Magedelight\Vendor\Api\Data\DashboardOverviewInterface;

class DashboardOverview extends \Magento\Framework\DataObject implements DashboardOverviewInterface
{

     /**
      * Get New Orders
      * @return int|null
      */
    public function getNewOrders()
    {
        return $this->getData(DashboardOverviewInterface::NEW_ORDERS);
    }

    /**
     * Set New Orders
     * @param int|null $newOrders
     * @return $this
     */
    public function setNewOrders($newOrders)
    {
        return $this->setData(DashboardOverviewInterface::NEW_ORDERS, $newOrders);
    }

    /**
     * Get To Be Shipped Orders
     * @return int|null
     */
    public function getToBeShipped()
    {
        return $this->getData(DashboardOverviewInterface::TO_BE_SHIPPED);
    }

    /**
     * Set Average Order
     *
     * @param int|null $toBeShipped
     * @return $this
     */
    public function setToBeShipped($toBeShipped)
    {
        return $this->setData(DashboardOverviewInterface::TO_BE_SHIPPED, $toBeShipped);
    }

    /**
     * Set Live Products
     * @param int|null $liveProducts
     * @return $this
     */
    public function setLiveProducts($liveProducts)
    {
        return $this->setData(DashboardOverviewInterface::LIVE_PRODUCTS, $liveProducts);
    }

    /**
     * Get Live Products
     * @return int|null
     */
    public function getLiveProducts()
    {
        return $this->getData(DashboardOverviewInterface::LIVE_PRODUCTS);
    }

    /**
     * Set Out of Stock products
     * @param int|null $outOfStock
     * @return $this
     */
    public function setOutOfStockProducts($outOfStock)
    {
        return $this->setData(DashboardOverviewInterface::OUT_OF_STOCK, $outOfStock);
    }

    /**
     * Get Out of Stock products
     * @return int|null
     */
    public function getOutOfStockProducts()
    {
        return $this->getData(DashboardOverviewInterface::OUT_OF_STOCK);
    }

    /**
     * Set Sales Summary
     * @param \Magedelight\Vendor\Data\SalesSummaryInterface[] $salesSummary
     * @return $this
     */
    public function setSalesSummary($summary)
    {
        return $this->setData(DashboardOverviewInterface::SALES_SUMMARY, $summary);
    }

    /**
     * Get Sales Summary
     * @return \Magedelight\Vendor\Data\SalesSummaryInterface[] $salesSummary
     */
    public function getSalesSummary()
    {
        return $this->getData(DashboardOverviewInterface::SALES_SUMMARY);
    }
}
