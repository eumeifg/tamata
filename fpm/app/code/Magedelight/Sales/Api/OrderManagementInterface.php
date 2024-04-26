<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Api;

/**
 * @api
 */
interface OrderManagementInterface
{

    /**
     * Vendor order confirm
     * @param int $vendorOrderId
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function confirmOrder($vendorOrderId);

    /**
     * Vendor order cancel
     * @param int $vendorOrderId
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function orderCancel($vendorOrderId);

    /**
     * Vendor order In Transit
     * @param int $vendorOrderId
     * @param string $status
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function orderStatusUpdate($vendorOrderId, $status);

    /**
     * Customer order cancel by customer
     * @param int $orderId
     * @param string|null $cancelOrderReason
     * @param string|null $cancelOrderComment
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     */
    public function cancelFullOrderByCustomer($orderId, $cancelOrderReason = null, $cancelOrderComment = null);

    /**
     * Customer order item by customer
     * @param int $orderId
     * @param int $orderItemId
     * @param string|null $cancelOrderItemReason
     * @param string|null $cancelOrderItemComment
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     */
    public function cancelOrderItemByCustomer(
        $orderId,
        $orderItemId,
        $cancelOrderItemReason = null,
        $cancelOrderItemComment = null
    );
}
