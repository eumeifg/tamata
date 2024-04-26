<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Quote\Model\Quote\Address\Item;

class Data extends AbstractHelper
{

    public function getQuoteAddrTotal($address, $totalKey, $vId)
    {
        if ($totalKey == 'base_subtotal') {
            $qiKey = 'base_row_total';
        } elseif ($totalKey == 'weight') {
            $qiKey = 'row_weight';
        } elseif ($totalKey == 'total_qty') {
            $qiKey = 'qty';
        } else {
            return false;
        }
        $total = 0;
        foreach ($address->getAllItems() as $item) {
            if ($item instanceof Item) {
                $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
            } else {
                $quoteItem = $item;
            }
            if (!$quoteItem->getParentItem() && in_array($quoteItem->getVendorId(), explode(',', $vId))) {
                $total += $quoteItem->getDataUsingMethod($qiKey);
            }
        }
        return $total;
    }
    
    /**
     *
     * @param string $path
     * @return boolean
     */
    public function getConfigValue($path = '', $storeId = null)
    {
        return ($path)?$this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId):'';
    }
    
    /**
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->getConfigValue('vendorpromotion/general/enable');
    }
    
    /**
     *
     * @return string
     */
    public function getExtensionKey()
    {
        return 'ek-rb-vendorpromotion';
    }
    
    /**
     *
     * @return string
     */
    public function getExtensionDisplayName()
    {
        return 'Vendor Promotion Add-on';
    }
}
