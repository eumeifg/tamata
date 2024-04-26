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

class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->taxConfig = $taxConfig;

        parent::__construct($context, $registry, $data);
    }

    public function getSource()
    {
        return $this->getVendorOrder();
    }

    public function getVendorOrder()
    {
        $vendorOrder = $this->_coreRegistry->registry('vendor_order');
        if (!$vendorOrder) {
            $vendorOrder = $this->getParentBlock()->getVendorOrder();
        }
        return $vendorOrder;
    }

    protected function _updateTotalForVendor($total, $vendorOrder, $key, $label = false)
    {
        if (isset($this->_totals[$total]) && is_object($this->_totals[$total])) {
            if ($this->taxConfig->displaySalesTaxWithGrandTotal()) {
                switch ($total) {
                    case "grand_total":
                        $this->_totals[$total]->setData(
                            'value',
                            $vendorOrder->getData($key) - $vendorOrder->getData('tax_amount')
                        );
                        $this->_totals[$total]->setData(
                            'base_value',
                            $vendorOrder->getData($key) - $vendorOrder->getData('base_tax_amount') -
                            $vendorOrder->getData('base_shipping_tax_amount')
                        );
                        break;
                    default:
                        $this->_totals[$total]->setData('value', $vendorOrder->getData($key));
                        $this->_totals[$total]->setData('base_value', $vendorOrder->getData($key));
                        break;
                }
            } else {
                $this->_totals[$total]->setData('value', $vendorOrder->getData($key));
                $this->_totals[$total]->setData('base_value', $vendorOrder->getData($key));
            }
            if ($label) {
                $this->_totals[$total]->setData('label', $label);
            }
        }
    }
}
