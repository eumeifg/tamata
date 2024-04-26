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
namespace Magedelight\Sales\Block\Sellerhtml\Order\Creditmemo\Create;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Adjustments extends \Magedelight\Backend\Block\Template
{
    /**
     * Source object
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * Tax config
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_taxConfig = $taxConfig;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * Initialize creditmemo agjustment totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_source = $parent->getSource();
        $total = new \Magento\Framework\DataObject(['code' => 'agjustments', 'block_name' => $this->getNameInLayout()]);
        $parent->removeTotal('shipping');
        $parent->removeTotal('adjustment_positive');
        $parent->removeTotal('adjustment_negative');
        $parent->addTotal($total);
        return $this;
    }

    /**
     * Get source object
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Get credit memo shipping amount depend on configuration settings
     *
     * @return float
     */
    public function getShippingAmount()
    {
        $source = $this->getSource();
        if ($this->_taxConfig->displaySalesShippingInclTax($source->getOrder()->getStoreId())) {
            $shipping = $source->getBaseShippingInclTax();
        } else {
            $shipping = $source->getBaseShippingAmount();
        }
        return $this->priceCurrency->round($shipping) * 1;
    }

    /**
     * Get label for shipping total based on configuration settings
     *
     * @return string
     */
    public function getShippingLabel()
    {
        $source = $this->getSource();
        if ($this->_taxConfig->displaySalesShippingInclTax($source->getOrder()->getStoreId())) {
            $label = __('Refund Shipping (Incl. Tax)');
        } elseif ($this->_taxConfig->displaySalesShippingBoth($source->getOrder()->getStoreId())) {
            $label = __('Refund Shipping (Excl. Tax)');
        } else {
            $label = __('Refund Shipping');
        }
        return $label;
    }
}
