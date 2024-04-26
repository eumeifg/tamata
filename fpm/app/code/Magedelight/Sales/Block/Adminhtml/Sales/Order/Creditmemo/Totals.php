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
namespace Magedelight\Sales\Block\Adminhtml\Sales\Order\Creditmemo;

/**
 * Adminhtml order creditmemo totals block
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Totals
{
    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $vendorOrder = $this->getSource()->getVendorOrder();
        if (!$vendorOrder) {
            $vendorOrder = $this->getParentBlock()->getSource()->getVendorOrder();
        }
        
        if ($vendorOrder) {
            /**
             * Add Shipping for vendor
             */
            if (!$this->getSource()->getIsVirtual() && !isset($this->_totals['shipping'])) {
                $this->_totals['shipping'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'shipping',
                        'value' => $this->getSource()->getShippingAmount(),
                        'base_value' => $this->getSource()->getBaseShippingAmount(),
                        'label' => __('Shipping & Handling'),
                    ]
                );
            }
            $this->setVendorOrder($vendorOrder);
            $this->_updateTotalForVendor('subtotal', $vendorOrder, 'subtotal');
            $this->_updateTotalForVendor('shipping', $vendorOrder, 'shipping_amount');
            //$this->_updateTotalForVendor('giftwrap', $vendorOrder, 'giftwrap_amount');
            $this->_updateTotalForVendor('discount', $vendorOrder, 'discount_amount', 'Discount');
            $this->_updateTotalForVendor('grand_total', $vendorOrder, 'grand_total');
        }
        return $this;
    }
    protected function _updateTotalForVendor($total, $vendorOrder, $key, $label = false)
    {
        if (isset($this->_totals[$total]) && is_object($this->_totals[$total]) || $total == 'shipping') {
            $this->_totals[$total]->setData('value', $vendorOrder->getData($key));
            $this->_totals[$total]->setData('base_value', $vendorOrder->getData($key));
            if ($label) {
                $this->_totals[$total]->setData('label', $label);
            }
        }
    }
    
    public function getTotals($area = null)
    {
        $totals = parent::getTotals($area);
        $vendorOrder = $this->getSource()->getVendorOrder();
        foreach ($totals as &$total) {
            if ($total['code'] == 'shipping') {
                $total->setData('value', $vendorOrder->getData('shipping_amount'));
                $total->setData('base_value', $vendorOrder->getData('shipping_amount'));
            } elseif ($total['code'] == 'tax') {
                $total->setData('value', $vendorOrder->getData('tax_amount'));
                $total->setData('base_value', $vendorOrder->getData('tax_amount'));
            } else {
                $total->setData('value', $vendorOrder->getData($total['code']));
                $total->setData('base_value', $vendorOrder->getData($total['code']));
            }
        }
        return $totals;
    }
}
