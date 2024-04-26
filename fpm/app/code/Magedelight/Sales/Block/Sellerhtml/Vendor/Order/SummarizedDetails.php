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
namespace Magedelight\Sales\Block\Sellerhtml\Vendor\Order;

class SummarizedDetails extends \Magento\Framework\View\Element\Template
{
    public function getSummarizedOrderData()
    {
        $vendorSkus = [];
        if ($this->getOrder()) {
            foreach ($this->getOrder()->getItems() as $item) {
                if ($item->getVendorId() != $this->getVendor()->getVendorId() || $item->getParentItem()) {
                    continue;
                }
                $vendorSkus[$item->getVendorSku()]['qty'] = $item->getQtyOrdered();
                $vendorSkus[$item->getVendorSku()]['row_total'] = $item->getRowTotal();
            }
        }
        return $vendorSkus;
    }
    
    /**
     *
     * @return string
     */
    public function getOrderCurrency()
    {
        return $this->getOrder()->getOrderCurrencyCode();
    }
}
