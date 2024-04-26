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
namespace MDC\Sales\Controller\Sellerhtml\Order;

use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magento\Sales\Model\Order as CoreOrder;
use Magento\Sales\Model\Order\Item as CoreOrderItem;

class MassAction extends \Magedelight\Sales\Controller\Sellerhtml\Order\MassAction
{
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_dbTransaction;

    /**
     *
     * @var \Magedelight\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * MassAction constructor.
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\DB\Transaction $dbTransaction
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\DB\Transaction $dbTransaction
    ) {
        $this->_orderFactory = $orderFactory->create();
        $this->_dbTransaction = $dbTransaction;
        parent::__construct($context, $orderFactory, $dbTransaction);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $action = $this->getRequest()->getParam('order_mass_action');
        $orderIds = $this->getRequest()->getParam('order_ids');
        if (!is_array($orderIds) || empty($orderIds) && empty($action)) {
            $this->messageManager->addErrorMessage(__('Please select order(s).'));
        } else {
            try {
                if ($action == 'confirm') {

                    foreach ($orderIds as $vendorOrderId) {

                        $vendorOrder = $this->_orderFactory->load($vendorOrderId);
                        $vendorOrder->setData('is_confirmed', 1);
                        $vendorOrder->setData('confirmed_at', date('Y-m-d'));
                        $vendorOrder->save();
                        $this->_eventManager->dispatch('vendor_orders_confirm_after', ['vendor_order' => $vendorOrder]);
                        $resultRedirect->setPath('*/*/index', ['tab' => '2,0']);
                    }

                    $this->messageManager->addSuccessMessage(
                        __('%1 Order Confirmed successfully.', count($orderIds))
                    );
                } elseif ($action == 'cancel') {

                    foreach ($orderIds as $key => $vendorOrderId) {
                        /** @var \Magedelight\Sales\Model\Order $vendorOrder */
                        $vendorOrder = $this->_orderFactory->load($vendorOrderId);
                        $order = $vendorOrder->getOriginalOrder();

                        if (!$vendorOrder->canCancel()) {
                            unset($orderIds[$key]);
                            continue;
                        }
                        $vendorOrder->registerCancel($order);

                        /*** Added fixes for order cancel issue when coupon code applied unable to cancel ***/
                        $cancelledItems = [];
                        $itemsCount = [];
                        $i = 0; $j = 0;
                        foreach ($order->getAllItems() as $item) {
                            if ($item->getParentItemId()) {
                                continue;
                            }
                            $itemsCount[] =  $item->getId();
                            if ($item->getStatusId() === CoreOrderItem::STATUS_CANCELED) {
                                $cancelledItems[] = $item->getId();
                            }
                            if ($item->getQtyOrdered() > ($item->getQtyCanceled() + $item->getQtyRefunded() + $item->getQtyInvoiced())) {
                                $j = 1; $j += $i;
                                $i++;
                            }
                        }

                        if (count($itemsCount) === count($cancelledItems)) {
                            $state = CoreOrder::STATE_CANCELED;
                            $order->setState($state)
                                ->setStatus($order->getConfig()->getStateDefaultStatus($state));
                        }
                        /*** Added fixes for order cancel issue when coupon code applied unable to cancel ***/

                        $vendorOrder->setData('cancelled_by', CancelledBy::SELLER);
                        $this->_dbTransaction->addObject($vendorOrder)
                            ->addObject($order)
                            ->save();
                        if ($j === 0) {
                            $refundedSc = $order->getBaseCustomerBalanceAmount() - ($order->getBaseCustomerBalanceInvoiced() + $order->getBaseCustomerBalanceRefunded());
                            if ($refundedSc > .0001) {
                                $this->_eventManager->dispatch('refund_store_credit_order_cancel_after', ['order' => $order, "sub_order_total"=> $refundedSc, 'vendor_order_id' => $vendorOrderId]);
                            }
                        }
                    }
                    $this->_eventManager->dispatch('vendor_orders_cancel_after', ['vendor_order_ids' => $orderIds]);

                    $this->messageManager->addSuccessMessage(
                        __('%1 Order(s) canceled.', count($orderIds))
                    );
                    $resultRedirect->setPath('*/*/index', ['tab' => '2,0']);
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
    }
}
