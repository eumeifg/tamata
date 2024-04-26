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
namespace Magedelight\Sales\Block\Adminhtml\Sales\Order\Creditmemo\Create;

/**
 * Adminhtml credit memo items grid
 */
class Items extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items
{
    /**
     * Get price data object
     *
     * @return Order|mixed
     */
    public function getPriceDataObject()
    {
        return $this->getCreditmemo()->getVendorOrder();
    }

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
                'order_id' => $this->getCreditmemo()->getOrderId(),
                'do_as_vendor' => $this->getRequest()->getParam('do_as_vendor', null),
                'vendor_order_id' => $this->getPriceDataObject()->getVendorOrderId(),
                'invoice_id' => $this->getRequest()->getParam('invoice_id', null)
            ]
        );
    }
}
