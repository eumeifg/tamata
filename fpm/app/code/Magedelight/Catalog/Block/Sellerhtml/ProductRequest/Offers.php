<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Block\Sellerhtml\ProductRequest;

class Offers extends \Magedelight\Catalog\Block\Sellerhtml\ProductRequest\Edit
{

    public function getProductConditionOption()
    {
        
        return $this->_productCondition->toOptionArray();
    }
    
    public function isStoreEdit()
    {
        return ($this->_request->getParam('store'));
    }
    
    public function getNote()
    {
        
        return __('These data will be updated for all stores of current website.');
    }

    public function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->getCurrencyCode();
    }

    public function getCurrencyDesc()
    {
        //$this->_storeManager->getStore()->getBaseCurrency()->getRate('UAH');
        $CC = $this->_storeManager->getStore()->getAvailableCurrencyCodes();

        $output = '';
        foreach ($CC as $key => $value) {
            $output .= $value.' : '.round($this->_storeManager->getStore()->getBaseCurrency()->getRate($value))."</br>";
        }
        return $output;
    }
}
