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
namespace Magedelight\Sales\Controller\Sellerhtml\Order;

use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;

class MassAction extends \Magedelight\Backend\App\Action
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
        parent::__construct($context);
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
                        $vendorOrder = $this->_orderFactory->load($vendorOrderId);
                        $order = $vendorOrder->getOriginalOrder();

                        if (!$vendorOrder->canCancel()) {
                            unset($orderIds[$key]);
                            continue;
                        }
                        $vendorOrder->registerCancel($order);
                        $vendorOrder->setData('cancelled_by', CancelledBy::SELLER);
                        $this->_dbTransaction->addObject($vendorOrder)
                            ->addObject($order)
                            ->save();
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
