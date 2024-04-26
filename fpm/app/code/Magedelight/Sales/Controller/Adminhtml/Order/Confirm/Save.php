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
namespace Magedelight\Sales\Controller\Adminhtml\Order\Confirm;

/**
 * Description of Save
 *
 * @author Rocket Bazaar Core Team
 */
class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
    ) {
        parent::__construct($context);
        $this->vendorOrderRepository = $vendorOrderRepository;
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $vendorId = $this->getRequest()->getParam('do_as_vendor');
        if ($this->getRequest()->getParam('vendor_order_id')) {
            $vendorOrder = $this->vendorOrderRepository->getById(
                $this->getRequest()->getParam('vendor_order_id')
            );
        } else {
            $vendorOrder = $this->vendorOrderRepository->getByOriginalOrderId($orderId, $vendorId);
        }

        if ($vendorOrder->getId() && $vendorOrder->getData("is_confirmed") != 1) {
            try {
                $vendorOrder->setData("is_confirmed", 1)->save();
                $this->messageManager->addSuccessMessage(
                    __('The vendor order has been confirmed.')
                );
                $this->_eventManager->dispatch('vendor_orders_confirm_after', ['vendor_order' => $vendorOrder]);
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $this->messageManager->addErrorMessage(__('The vendor order could not confirm.'));
            }
        }
        $this->_redirect('sales/order/view', ['order_id' => $orderId]);
    }
}
