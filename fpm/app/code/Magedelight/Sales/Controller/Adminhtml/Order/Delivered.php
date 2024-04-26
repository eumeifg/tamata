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

use Magedelight\Sales\Model\Order as VendorOrder;

class Delivered extends \Magento\Backend\App\Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magedelight\Sales\Model\OrderFactory
     */
    protected $_vendorOrder;

    protected $vendorOrderRepository;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Sales\Model\OrderFactory $vendorOrder,
        \Psr\Log\LoggerInterface $logger,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
    ) {
        $this->_vendorOrder = $vendorOrder->create();
        $this->_logger = $logger;
        $this->vendorOrderRepository = $vendorOrderRepository;
        parent::__construct($context);
    }
    
    /**
     * Change order status to handover
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $vendorId = $this->getRequest()->getParam('do_as_vendor');
        //$vendorOrder = $this->_vendorOrder->getByOriginOrderId($orderId, $vendorId);
        if ($this->getRequest()->getParam('vendor_order_id')) {
            $vendorOrder = $this->vendorOrderRepository->getById(
                $this->getRequest()->getParam('vendor_order_id')
            );
        } else {
            $vendorOrder = $this->vendorOrderRepository->getByOriginalOrderId($orderId, $vendorId);
        }
        
        if ($vendorOrder->getId()) {
            try {
                $vendorOrder->setData("status", VendorOrder::STATUS_COMPLETE)
                    ->save();
                $this->messageManager->addSuccess(
                    __('Vendor order status changed to delivered.')
                );
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $this->messageManager->addError(__('Vendor order status cannot be changed to delivered'));
            }
        }
        $this->_redirect('sales/order/view', ['order_id' => $orderId]);
    }
}
