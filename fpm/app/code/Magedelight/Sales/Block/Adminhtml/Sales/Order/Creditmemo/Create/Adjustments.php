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

class Adjustments extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Adjustments
{
    /**
     * Get credit memo shipping amount depend on configuration settings
     *
     * @return float
     */
    public function getShippingAmount()
    {
        $source = $this->getSource();
        if ($this->_taxConfig->displaySalesShippingInclTax($source->getOrder()->getStoreId())) {
            $shipping = $source->getVendorOrder()->getBaseShippingInclTax();
        } else {
            $shipping = $source->getVendorOrder()->getBaseShippingAmount();
        }
        return $this->priceCurrency->round($shipping) * 1;
    }
}
