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
namespace Magedelight\Sales\Block\Adminhtml\Sales\Order\Invoice\View;

/**
 * Invoice view form
 *
 * @author Rocket Bazaar Core Team
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Invoice\View\Form
{
    /**
     * Retrieve order url
     *
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl(
            'rbsales/order/view',
            ['order_id' => $this->getInvoice()->getOrderId(), 'vendor_id' => $this->getInvoice()->getVendorId()]
        );
    }
}
