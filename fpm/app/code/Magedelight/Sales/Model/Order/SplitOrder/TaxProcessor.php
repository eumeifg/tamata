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
namespace Magedelight\Sales\Model\Order\SplitOrder;

/**
 * Process TAX/VAT for sub order data.
 * @author Rocket Bazaar Core Team
 * Created at 31 Dec , 2019 11:30:00 AM
 */
class TaxProcessor extends \Magento\Framework\DataObject
{
    /**
     * @param array $subOrderData
     * @return array
     */
    public function process($subOrderData)
    {
        $subOrderData->setTaxAmount($subOrderData->getTaxAmount() + $subOrderData->getShippingTaxAmount());
        $subOrderData->setBaseTaxAmount($subOrderData->getBaseTaxAmount() + $subOrderData->getBaseShippingTaxAmount());
        $itemTaxAmount = $subOrderData->getSubtotalInclTax() - $subOrderData->getSubtotal();
        $baseItemTaxAmount = $subOrderData->getBaseSubtotalInclTax() - $subOrderData->getBaseSubtotal();

        $tax = min(
            round($subOrderData->getTaxAmount(), 2),
            (floor(($itemTaxAmount + $subOrderData->getShippingTaxAmount())  * 100) / 100)
        );
        $baseTax = min(
            round($subOrderData->getBaseTaxAmount(), 2),
            (floor(($baseItemTaxAmount + $subOrderData->getBaseShippingTaxAmount()) * 100) / 100)
        );
        $subOrderData->setTaxAmount($tax);
        $subOrderData->setBaseTaxAmount($baseTax);

        $subOrderData->setGrandTotal(
            $subOrderData->getGrandTotal() + $subOrderData->getTaxAmount() +
            $subOrderData->getDiscountTaxCompensationAmount()
        );
        $subOrderData->setBaseGrandTotal(
            $subOrderData->getBaseGrandTotal() + $subOrderData->getBaseTaxAmount() +
            $subOrderData->getBaseDiscountTaxCompensationAmount()
        );
        $subOrderData->setBaseSubtotalInclTax(
            $subOrderData->getBaseSubtotalInclTax() + $subOrderData->getBaseTaxAmount() +
            $subOrderData->getBaseDiscountTaxCompensationAmount()
        );
        return $subOrderData;
    }
}
