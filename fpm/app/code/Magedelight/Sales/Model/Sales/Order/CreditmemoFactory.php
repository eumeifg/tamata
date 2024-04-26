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
namespace Magedelight\Sales\Model\Sales\Order;

/**
 * Factory class for @see \Magento\Sales\Model\Order\Creditmemo
 */
class CreditmemoFactory extends \Magento\Sales\Model\Order\CreditmemoFactory
{
    /**
     * Prepare order creditmemo based on order items and requested params
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Magedelight\Sales\Model\Order $vendorOrder
     * @param array $data
     * @return Creditmemo
     */
    public function createByVendorOrder(
        \Magento\Sales\Model\Order $order,
        \Magedelight\Sales\Model\Order $vendorOrder,
        array $data = []
    ) {
        $totalQty = 0;
        $creditmemo = $this->convertor->toCreditmemo($order);
        $qtys = isset($data['qtys']) ? $data['qtys'] : [];
        $isNewReq = empty($qtys) ? true : false;
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getData('vendor_id') != $vendorOrder->getVendorId()) {
                $qtys[$orderItem->getId()] = 0;
            } elseif ($isNewReq) {
                $qtys[$orderItem->getId()] = $orderItem->getQtyToRefund();
            }
            if (!$this->canRefundItem($orderItem, $qtys)) {
                continue;
            }
            $item = $this->convertor->itemToCreditmemoItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = 1;
                $orderItem->setLockedDoShip(true);
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = (double)$qtys[$orderItem->getId()];
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyToRefund();
                } else {
                    continue;
                }
            }
            $totalQty += $qty;
            $item->setQty($qty);
            $creditmemo->addItem($item);
        }
        $creditmemo->setTotalQty($totalQty);
        $this->initData($creditmemo, $data);

        $creditmemo->collectTotals();
        return $creditmemo;
    }
}
