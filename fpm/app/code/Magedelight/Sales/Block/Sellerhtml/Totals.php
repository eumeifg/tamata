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
namespace Magedelight\Sales\Block\Sellerhtml;

class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * Fronend Helper
     *
     * @var \Magedelight\Sales\Helper\Frontend
     */
    protected $_frontendHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magedelight\Sales\Helper\Frontend $frontendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Sales\Helper\Frontend $frontendHelper,
        array $data = []
    ) {
        $this->_frontendHelper = $frontendHelper;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Format total value based on order currency
     *
     * @param \Magento\Framework\DataObject $total
     * @return string
     */
    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            return $this->_frontendHelper->displayPrices($this->getOrder(), $total->getBaseValue(), $total->getValue());
        }
        return $total->getValue();
    }

    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        $this->_totals = [];
        $this->_totals['subtotal'] = new \Magento\Framework\DataObject(
            [
                'code' => 'subtotal',
                'value' => $this->getSource()->getSubtotal(),
                'base_value' => $this->getSource()->getBaseSubtotal(),
                'label' => __('Subtotal'),
            ]
        );

        /**
         * Add shipping
         */
        if (!$this->getSource()->getIsVirtual() && ((double)$this->getSource()->getShippingAmount() ||
            $this->getSource()->getShippingDescription())
        ) {
            $this->_totals['shipping'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'shipping',
                    'value' => $this->getSource()->getShippingAmount(),
                    'base_value' => $this->getSource()->getBaseShippingAmount(),
                    'label' => __('Shipping & Handling'),
                ]
            );
        }

        /**
         * Add discount
         */
        if ((double)$this->getSource()->getDiscountAmount() != 0) {
            if ($this->getSource()->getDiscountDescription()) {
                $discountLabel = __('Discount (%1)', $this->getSource()->getDiscountDescription());
            } else {
                $discountLabel = __('Discount');
            }
            $this->_totals['discount'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'discount',
                    'value' => $this->getSource()->getDiscountAmount(),
                    'base_value' => $this->getSource()->getBaseDiscountAmount(),
                    'label' => $discountLabel,
                ]
            );
        }

        $this->_totals['grand_total'] = new \Magento\Framework\DataObject(
            [
                'code' => 'grand_total',
                'strong' => true,
                'value' => $this->getSource()->getGrandTotal(),
                'base_value' => $this->getSource()->getBaseGrandTotal(),
                'label' => __('Grand Total'),
                'area' => 'footer',
            ]
        );

        return $this;
    }
}
