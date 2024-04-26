<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Tax
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Tax\Plugin\Model\Sales\Total\Quote;

use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Framework\App\ObjectManager;

class CommonTaxCollector
{

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**#@-*/
    protected $_config;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param TaxHelper|null $taxHelper
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        TaxHelper $taxHelper = null,
        \Magento\Framework\App\State $appState
    ) {
        $this->_config = $taxConfig;
        $this->taxHelper = $taxHelper ?: ObjectManager::getInstance()->get(TaxHelper::class);
        $this->appState = $appState;
    }

    /**
     * Update tax related fields for quote item
     *
     * @param AbstractItem $quoteItem
     * @param TaxDetailsItemInterface $itemTaxDetails
     * @param TaxDetailsItemInterface $baseItemTaxDetails
     * @param Store $store
     * @return $this
     */
    public function aroundUpdateItemTaxInfo(\Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector $subject, \Closure $proceed, $quoteItem, $itemTaxDetails, $baseItemTaxDetails, $store)
    {
        //The price should be base price
        $quoteItem->setPrice($baseItemTaxDetails->getPrice());
        if ($quoteItem->getCustomPrice() &&
            $this->taxHelper->applyTaxOnCustomPrice() &&
            $this->appState->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML
        ) {
            $quoteItem->setCustomPrice($baseItemTaxDetails->getPrice());
        }
        $quoteItem->setConvertedPrice($itemTaxDetails->getPrice());
        $quoteItem->setPriceInclTax($itemTaxDetails->getPriceInclTax());
        $quoteItem->setRowTotal($itemTaxDetails->getRowTotal());
        $quoteItem->setRowTotalInclTax($itemTaxDetails->getRowTotalInclTax());
        $quoteItem->setTaxAmount($itemTaxDetails->getRowTax());
        $quoteItem->setTaxPercent($itemTaxDetails->getTaxPercent());
        $quoteItem->setDiscountTaxCompensationAmount($itemTaxDetails->getDiscountTaxCompensationAmount());

        $quoteItem->setBasePrice($baseItemTaxDetails->getPrice());
        $quoteItem->setBasePriceInclTax($baseItemTaxDetails->getPriceInclTax());
        $quoteItem->setBaseRowTotal($baseItemTaxDetails->getRowTotal());
        $quoteItem->setBaseRowTotalInclTax($baseItemTaxDetails->getRowTotalInclTax());
        $quoteItem->setBaseTaxAmount($baseItemTaxDetails->getRowTax());
        $quoteItem->setTaxPercent($baseItemTaxDetails->getTaxPercent());
        $quoteItem->setBaseDiscountTaxCompensationAmount($baseItemTaxDetails->getDiscountTaxCompensationAmount());

        //Set discount calculation price, this may be needed by discount collector
        if ($this->_config->discountTax($store)) {
            $quoteItem->setDiscountCalculationPrice($itemTaxDetails->getPriceInclTax());
            $quoteItem->setBaseDiscountCalculationPrice($baseItemTaxDetails->getPriceInclTax());
        } else {
            $quoteItem->setDiscountCalculationPrice($itemTaxDetails->getPrice());
            $quoteItem->setBaseDiscountCalculationPrice($baseItemTaxDetails->getPrice());
        }

        return $subject;
    }
}
