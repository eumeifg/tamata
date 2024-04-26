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
namespace Magedelight\Sales\Controller\Adminhtml\Order;

use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;

class Cancel extends \Magento\Sales\Controller\Adminhtml\Order
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
                $payment = $order->getPayment();
                $payment->setBaseAmountAuthorized($vendorOrder->getGrandTotal());
                $payment->setAmountAuthorized($vendorOrder->getGrandTotal());
                $payment->cancel();
                $vendorOrder->registerCancel($order);
                $vendorOrder->setData('cancelled_by', CancelledBy::MERCHANT);
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
}
