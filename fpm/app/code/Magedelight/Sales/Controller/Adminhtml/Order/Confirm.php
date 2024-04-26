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

class Confirm extends \Magento\Sales\Controller\Adminhtml\Order
{
    public function execute()
    {
        $order = $this->_initOrder();
        
        if ($order) {
            try {
                $order->setData('is_confirmed', 1)->save();
                $eventParams = ['order' => $order];
                $this->_eventManager->dispatch('vendor_order_admin_confirm', $eventParams);
                $this->messageManager->addSuccess(
                    __('The order has been confirmed.')
                );
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addError(__($e->getMessage()));
            }
            $this->_redirect('sales/order/view', ['order_id' => $order->getId()]);
        }
    }
}
