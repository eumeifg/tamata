<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MDC\Sales\Observer;

/**
 * Customer balance observer
 */
class RevertStoreCreditForOrder
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CustomerBalance\Model\BalanceFactory
     */
    protected $_balanceFactory;

     /**
     * @var \Magedelight\Sales\Model\CheckStoreCreditHistory
     */
    protected $creditHistoryModel;

    /**
     * Constructor
     *
     * @param \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Sales\Model\CheckStoreCreditHistory $creditHistoryModel
    ) {
        $this->_balanceFactory = $balanceFactory;
        $this->_storeManager = $storeManager;
        $this->creditHistoryModel = $creditHistoryModel;
    }

    /**
     * Revert authorized store credit amount for order
     *
     * @param   \Magento\Sales\Model\Order $order
     * @return  $this
     */
    public function execute(\Magento\Sales\Model\Order $order,$subOrderTotal, $vendorOrderId)
    {
        /*if (!$order->getCustomerId() || !$order->getBaseCustomerBalanceAmount()) {
            return $this;
        }*/

        if (!$order->getCustomerId() ) {
            return $this;
        }

        $customerRefundAmount = $this->creditHistoryModel->getCustomerRefundableAmount($order, $subOrderTotal, $vendorOrderId);
        if( $customerRefundAmount > 0 ){

            $this->_balanceFactory->create()->setCustomerId(
                $order->getCustomerId()
            )->setWebsiteId(
                $this->_storeManager->getStore($order->getStoreId())->getWebsiteId()
            )->setAmountDelta(
                $customerRefundAmount
            )->setHistoryAction(
                \Magento\CustomerBalance\Model\Balance\History::ACTION_REVERTED
            )->setOrder(
                $order
            )->save();
            $order->setBsCustomerBalTotalRefunded(($order->getBsCustomerBalTotalRefunded() + $customerRefundAmount));
            $order->setCustomerBalTotalRefunded(($order->getCustomerBalTotalRefunded() + $customerRefundAmount));
            $order->setBaseCustomerBalanceRefunded(($order->getBaseCustomerBalanceRefunded() + $customerRefundAmount));
            $order->setCustomerBalanceRefunded(($order->getCustomerBalanceRefunded() + $customerRefundAmount));
            $order->save();

            return $this;
        }

         
    }
}
