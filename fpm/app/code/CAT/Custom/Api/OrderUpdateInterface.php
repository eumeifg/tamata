<?php

namespace CAT\Custom\Api;

use Magento\Tests\NamingConvention\true\mixed;

interface OrderUpdateInterface {

    /**
     * update order custom fields.
     *
     * @param string $orderId
     * @param mixed $items
     * @return mixed
     */
    public function execute($orderId, $items);

    /**
     * @param string $id
     * @param mixed $items
     * @return mixed
     */
    public function updateSubOrder($id, $items);

    /**
     * @param int $vendorOrderId
     * @param int $pickupStatus
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateStatus($vendorOrderId, $pickupStatus);

    /**
     * @param int $vendorOrderId
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function warehouseOrderDelivery($vendorOrderId);

    /**
     * @param mixed $subOrderIds
     * @param int $isPaid
     * @return mixed
     */
    public function bulkUpdateInvoicePaid($subOrderIds, $isPaid);

    /**
     * @param int $id
     * @return mixed
     */
    public function checkSubOrder($id);

    /**
     * @param mixed $items
     * @return mixed
     */
    public function updateBulkSubOrder($items);

    /**
     * @param int $vendorOrderId
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancelSubOrder($vendorOrderId);
}
