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
namespace Magedelight\Sales\Block\Adminhtml\Order;

class Totals extends \Magento\Sales\Block\Adminhtml\Totals
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
    }
    protected function _initTotals()
    {
        parent::_initTotals();
        $vendorOrder = $this->_coreRegistry->registry('vendor_order');
        if (!$vendorOrder) {
            $vendorOrder = $this->getParentBlock()->getVendorOrder();
        }

        if ($vendorOrder) {
            $this->setVendorOrder($vendorOrder);
            $this->_updateTotalForVendor('subtotal', $vendorOrder, 'subtotal');
            $this->_updateTotalForVendor('shipping', $vendorOrder, 'shipping_amount');
            $this->_updateTotalForVendor('giftwrap', $vendorOrder, 'giftwrap_amount');
            $this->_updateTotalForVendor('discount', $vendorOrder, 'discount_amount', 'Discount');
            $this->_updateTotalForVendor('grand_total', $vendorOrder, 'grand_total');
        }

        return $this;
    }
    protected function _updateTotalForVendor($total, $vendorOrder, $key, $label = false)
    {
        if (isset($this->_totals[$total]) && is_object($this->_totals[$total])) {
            $this->_totals[$total]->setData('value', $vendorOrder->getData($key));
            $this->_totals[$total]->setData('base_value', $vendorOrder->getData($key));
            if ($label) {
                $this->_totals[$total]->setData('label', $label);
            }
        }
    }
}
