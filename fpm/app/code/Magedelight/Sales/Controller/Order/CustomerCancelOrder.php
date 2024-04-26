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
namespace Magedelight\Sales\Controller\Order;

use Magento\Sales\Controller\OrderInterface;
use Magento\Framework\App\Action\Context;

/**
 * Controller for customer cancel order
 */
class CustomerCancelOrder extends \Magento\Framework\App\Action\Action implements OrderInterface
{

    /**
     * @var \Magedelight\Sales\Api\OrderManagementInterface
     */
    protected $vendorOrderManagement;

    /**
     * CustomerCancelOrder constructor.
     * @param Context $context
     * @param \Magedelight\Sales\Api\OrderManagementInterface $vendorOrderManagement
     */
    public function __construct(
        Context $context,
        \Magedelight\Sales\Api\OrderManagementInterface $vendorOrderManagement
    ) {
        $this->vendorOrderManagement = $vendorOrderManagement;
        parent::__construct($context);
    }

    /**
     * Cancel full order
     *
     * @return void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if (empty($this->getRequest()->getParam('order_cancel_reason'))) {
                $this->messageManager->addErrorMessage(
                    __('Please select the order cancellation reason.')
                );
                return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
            }
            $result = $this->vendorOrderManagement->cancelFullOrderByCustomer(
                $this->getRequest()->getParam('order_id'),
                $this->getRequest()->getParam('order_cancel_reason'),
                $this->getRequest()->getParam('comment')
            );
            if ($result->getStatus() == true) {
                $this->messageManager->addSuccessMessage($result->getMessage());
            } else {
                $this->messageManager->addErrorMessage($result->getMessage());
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Failed to cancel order.');
        }
        return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
    }
}
