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
namespace Magedelight\Sales\Block\Adminhtml\Sales\Order\Invoice\Create;

/**
 * Adminhtml invoice items grid
 */
class Items extends \Magento\Sales\Block\Adminhtml\Order\Invoice\Create\Items
{
    /**
     * Get update url
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl(
            'rbsales/*/updateQty',
            [
                'order_id' => $this->getInvoice()->getOrderId(),
                'do_as_vendor' => $this->getInvoice()->getVendorId(),
                'vendor_order_id' => $this->getVendorOrder()->getVendorOrderId()
            ]
        );
    }
    
    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getInvoice()->getOrder();
    }

    /**
     * Retrieve invoice vendor order
     *
     * @return \Magedelight\Sales\Model\Order
     */
    public function getVendorOrder()
    {
        return $this->getInvoice()->getVendorOrder();
    }
    
    /**
     * Retrieve price data object
     *
     * @return Order
     */
    public function getPriceDataObject()
    {
        return $this->getOrder();
    }
}
