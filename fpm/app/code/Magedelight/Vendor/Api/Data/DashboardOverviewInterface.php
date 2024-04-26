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
namespace Magedelight\Vendor\Api\Data;

/**
 * @api
 */
interface DashboardOverviewInterface
{
    const NEW_ORDERS = 'new_orders';
    const TO_BE_SHIPPED = 'to_be_shipped';
    const LIVE_PRODUCTS = 'live_products';
    const OUT_OF_STOCK = 'out_of_stock';
    const SALES_SUMMARY = 'sales_summary';
    
    /**
     * Get New Orders
     * @return int|null
     */
    public function getNewOrders();

    /**
     * Set New Orders
     * @param int|null $newOrders
     * @return $this
     */
    public function setNewOrders($newOrders);

    /**
     * Get To Be Shipped Orders
     * @return int|null
     */
    public function getToBeShipped();

    /**
     * Set Average Order
     *
     * @param int|null $toBeShipped
     * @return $this
     */
    public function setToBeShipped($toBeShipped);
    
    /**
     * Set Live Products
     * @param int|null $liveProducts
     * @return $this
     */
    public function setLiveProducts($liveProducts);
    
    /**
     * Get Live Products
     * @return int|null
     */
    public function getLiveProducts();

    /**
     * Set Out of Stock products
     * @param int|null $outOfStock
     * @return $this
     */
    public function setOutOfStockProducts($outOfStock);
    
    /**
     * Get Out of Stock products
     * @return int|null
     */
    public function getOutOfStockProducts();

    /**
     * Set Sales Summary
     * @param \Magedelight\Vendor\Api\Data\SalesSummaryInterface[] $amountPaid
     * @return $this
     */
    public function setSalesSummary($amountPaid);
    
    /**
     * Get Sales Summary
     * @return \Magedelight\Vendor\Api\Data\SalesSummaryInterface[] Array of sales summary
     */
    public function getSalesSummary();
}
