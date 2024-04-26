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
namespace MDC\Sales\Controller\Adminhtml\Order;

use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magedelight\Sales\Model\Order as VendorOrderModel;
use Magento\Sales\Model\Order as CoreOrder;
use Magento\Sales\Model\Order\Item as CoreOrderItem;

class Cancel extends \Magedelight\Sales\Controller\Adminhtml\Order\Cancel
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $order = $this->_initOrder();
        $vendorOrder = $this->_objectManager->create(\Magedelight\Sales\Api\OrderRepositoryInterface::class)
            ->getById($this->getRequest()->getParam('vendor_order_id'));
            
        if ($order && $vendorOrder && $vendorOrder->canCancel()) {
            try {
                $this->_eventManager->dispatch(
                    'admin_orders_cancel_after',
                    ['order' => $order, 'vendor_order' => $vendorOrder]
                );

                $customerBalanceDataHelper = $this->_objectManager->create(\Magento\CustomerBalance\Helper\Data::class);

                if($customerBalanceDataHelper->isAutoRefundEnabled() && $vendorOrder->getStatus() === VendorOrderModel::STATUS_PENDING || $vendorOrder->getStatus() === VendorOrderModel::STATUS_CONFIRMED|| $vendorOrder->getStatus() === VendorOrderModel::STATUS_PROCESSING ){
                    if($this->checkRemainingItems($order)) {
                        /* Store credit refund on cancel order by admin(do as vendor) */
                        $this->_eventManager->dispatch('refund_store_credit_order_cancel_after', ['order' => $order, "sub_order_total"=> $order->getBaseCustomerBalanceAmount(), 'vendor_order_id' => $this->getRequest()->getParam('vendor_order_id')]);
                        /* Store credit refund on cancel order by admin(do as vendor) */
                    }
                }
                $orderCancelledAfterInvAndShp = false;
                /*if order is cancelled after invoiced/shipment create credit memo manually first */
                if($vendorOrder->getStatus() === VendorOrderModel::STATUS_PACKED || $vendorOrder->getStatus() === VendorOrderModel::STATUS_SHIPPED || $vendorOrder->getStatus() === VendorOrderModel::STATUS_HANDOVER || $vendorOrder->getStatus() === VendorOrderModel::STATUS_IN_TRANSIT  || $vendorOrder->getStatus() === VendorOrderModel::STATUS_OUT_WAREHOUSE ){

                    $orderCancelledAfterInvAndShp = true;

                    $saveCreditMemo = $this->_objectManager->create(\MDC\Sales\Model\Sales\Order\Creditmemo\Save::class);
                     
                    $saveCreditMemo->generateCreditMemoWithCancelOrder($vendorOrder,$this->getRequest()->getParams(),$order);
                }
                /*if order is cancelled after invoiced/shipment create credit memo manually first */
 
                $payment = $order->getPayment();
                $payment->setBaseAmountAuthorized($vendorOrder->getGrandTotal());
                $payment->setAmountAuthorized($vendorOrder->getGrandTotal());
                $payment->cancel();
                $vendorOrder->registerCancel($order, $orderCancelledAfterInvAndShp);
                $vendorOrder->setData('cancelled_by', CancelledBy::MERCHANT);
                
                /*** Added fixes for order cancel issue when coupon code applied unable to cancel ***/
                $cancelledItems = [];
				$itemsCount = [];
				foreach ($order->getAllItems() as $item) {
					if ($item->getParentItemId()) {
						continue;
					}
					$itemsCount[] =  $item->getId();
					if ($item->getStatusId() === CoreOrderItem::STATUS_CANCELED) {
						$cancelledItems[] = $item->getId();
					}
				}
				
				if (count($itemsCount) === count($cancelledItems)) {
					$state = CoreOrder::STATE_CANCELED;
					$order->setState($state)
						->setStatus($order->getConfig()->getStateDefaultStatus($state));
				}
				/*** Added fixes for order cancel issue when coupon code applied unable to cancel ***/

                $this->_objectManager->create(
                    \Magento\Framework\DB\Transaction::class
                )->addObject($vendorOrder)
                ->addObject($order)
                ->save();
                $this->messageManager->addSuccessMessage(
                    __('The order has been canceled.')
                );


            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('The order has not been canceled.'));
            }
            return $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
        }
        return $resultRedirect->setPath('sales/*/');
    }

    public function checkRemainingItems(\Magento\Sales\Model\Order $order) {
        $i = 0; $j = 0;
        foreach ($order->getAllVisibleItems() as $item) {
            if($item->getQtyOrdered() > ($item->getQtyCanceled() + $item->getQtyRefunded())) {
                $j = 1; $j += $i; $i++;
            }
        }
        if ($j === 1) { return true; }
        return false;
    }
}
