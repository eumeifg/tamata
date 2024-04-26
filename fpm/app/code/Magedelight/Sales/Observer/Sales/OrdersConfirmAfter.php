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
namespace Magedelight\Sales\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;
use \Magedelight\Sales\Model\Order as VendorOrder;

class OrdersConfirmAfter implements ObserverInterface
{

    protected $_vendorOrder;

    /**
     *
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrder
     */
    public function __construct(
        \Magedelight\Sales\Model\OrderFactory $vendorOrder
    ) {
        $this->_vendorOrder = $vendorOrder;
    }
    
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getVendorOrder();
        try {
            $vendorOrder = $this->_vendorOrder->create()->load($order->getId());
            $vendorOrder->setData('status', VendorOrder::STATUS_PROCESSING);
            $vendorOrder->save();
        } catch (\Exception $ex) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t save the invoice right now.')
            );
        }
    }
}
