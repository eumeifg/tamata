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
 * Adminhtml creditmemo create form
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Form
{
    /**
     * Get save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('rbsales/*/save', ['_current' => true,]);
    }
    
    /**
     * Get price data object
     *
     * @return Order|mixed
     */
    public function getPriceDataObject()
    {
        $obj = $this->getData('price_data_object');
        if ($obj === null) {
            return $this->getCreditmemo()->getVendorOrder();
        }
        return $obj;
    }
}
