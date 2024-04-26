<?php
 
namespace MDC\Sales\Controller\Order;

use Magento\Sales\Controller\OrderInterface;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Controller for cancel order
 */
class CustomerCancelOrderItem extends \Magedelight\Sales\Controller\Order\CustomerCancelOrderItem
{

    /**
     * @var \Magedelight\Sales\Api\OrderManagementInterface
     */
    protected $vendorOrderManagement;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\CustomerBalance\Helper\Data
     */
    protected $customerBalanceDataHelper;

    public function __construct(
        Context $context,
        \Magedelight\Sales\Api\OrderManagementInterface $vendorOrderManagement,
        OrderRepositoryInterface $orderRepository,
        \Magento\CustomerBalance\Helper\Data $customerBalanceDataHelper
    ) {        
        $this->orderRepository = $orderRepository;
        $this->customerBalanceDataHelper = $customerBalanceDataHelper;
        parent::__construct($context,$vendorOrderManagement);
    }
    
    /**
     * Cancel order items
     *
     * @return void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        $checkItemStatus = $this->checkRemainingItems($order);
        try {
            $result = $this->vendorOrderManagement->cancelOrderItemByCustomer(
                $orderId,
                $this->getRequest()->getParam('order_item_id'),
                $this->getRequest()->getParam('cancel_item_reason'),
                $this->getRequest()->getParam('cancel_item_comment')
            );
            if ($result->getStatus() == true) {
                if($this->customerBalanceDataHelper->isAutoRefundEnabled()){
                    if ($checkItemStatus) {
                        /* Store credit refund on cancel order by Customer */
                        $vendorOrderId = $this->getRequest()->getParam('vendor_order_id');
                        // $vendorOrder = $this->_objectManager->create(\Magedelight\Sales\Api\OrderRepositoryInterface::class)->getById($vendorOrderId);
                        $this->_eventManager->dispatch('refund_store_credit_order_cancel_after', ['order' => $order, "sub_order_total"=> $order->getBaseCustomerBalanceAmount(), 'vendor_order_id' => $vendorOrderId]);
                        //$this->_eventManager->dispatch('refund_store_credit_order_cancel_after', ['order' => $order, "sub_order_total"=> $vendorOrder->getGrandTotal(), 'vendor_order_id' => $vendorOrderId]);
                        /* Store credit refund on cancel order by Customer */
                    }
                }
                $this->messageManager->addSuccessMessage($result->getMessage());
            } else {
                $this->messageManager->addErrorMessage($result->getMessage());
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
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
