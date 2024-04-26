<?php

/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Review
 * @copyright Copyright (c) 2019 Magedelight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Sales\Api;

use Magedelight\Sales\Api\Data\VendorOrderInterface;

/**
 * @api
 */
interface OrderRepositoryInterface
{

    /**
     * Get vendor order by vendor order id.
     * @param integer $vendorOrderId
     * @return VendorOrderInterface
     */
    public function getById($vendorOrderId);

    /**
     * @param int|null $order_id;
     * @param int|null $type;
     * @param int|null $limit;
     * @param int|null $currPage;
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface Order search result interface.
     */
    public function getList($order_id = null, $type = null, $limit = null, $currPage = null);

   /**
    * Get all type of Orders for Vendor
    * @param string section
    * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
    * @param string|null $searchTerm
    * @return \Magedelight\Sales\Api\Data\VendorOrderSearchResultInterface
    */
    public function getVendorOrders($section, $searchCriteria = null, $searchTerm = null);

    /**
     * Get vendor order by original order id and vendor id.
     * @param integer $orderId
     * @param integer|NULL $vendorId
     * @return VendorOrderInterface
     */
    public function getByOriginalOrderId($orderId, $vendorId = null);

    /**
     * Get vendor order by vendor order id.
     * Used specifically in seller area/app.
     * Need to maintain the status as per vendor scope in seller area. Default is customer scope.
     * @param integer $vendorOrderId
     * @return VendorOrderInterface
     */
    public function getVendorOrderById($vendorOrderId);

    /**
     * Get Customer order cancel reasons
     * @return array
     */
    public function getCustomerOrderCancelReason();
}
