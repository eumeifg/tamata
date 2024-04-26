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
namespace Magedelight\Sales\Model\Order;

/**
 * Vendor Order Invoice model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Invoice extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    private $vendorHelper;
    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    private $vendorFactory;

    public function __construct(
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        array $data = []
    ) {
        $this->vendorHelper = $vendorHelper;
        $this->vendorFactory = $vendorFactory->create();
    }

    public function processInvoice($order, $invoice, $vendorOrder, $invoiceItems)
    {
        $vendorOrderQty = $invoiceQty = $orderShippingAmount = $orderShippingTaxAmount = $orderBaseShippingAmount = 0;
        $orderBaseShippingTaxAmount = $giftwrapperAmount = $baseGiftwrapperAmount = $discountAmount = 0;
        $baseDiscountAmount = $storeCreditAmount = $baseStoreCreditAmount = 0;
        $invoiceBaseGrandTotal = $invoiceGrandTotal = $tax = $baseTax = 0;
        foreach ($order->getItemsCollection() as $item) {
            if ($item->getData('vendor_order_id') != $vendorOrder->getVendorOrderId()) {
                $invoiceItems[$item->getId()] = 0;
            } else {
                $vendorOrderQtyVendor = $item->getQtyOrdered();
                $vendorOrderQty += $item->getQtyOrdered();
                if (array_key_exists($item->getId(), $invoiceItems)) {
                    if ($order->getShippingMethod() == 'rbmatrixrate_rbmatrixrate') {
                        $orderShippingAmount += $vendorOrder->getShippingAmount() *
                            ($invoiceItems[$item->getId()]  / $item->getQtyOrdered());
                        $orderBaseShippingAmount += $vendorOrder->getBaseShippingAmount() *
                            ($invoiceItems[$item->getId()] / $item->getQtyOrdered());
                        $orderShippingTaxAmount += $vendorOrder->getShippingTaxAmount() *
                            ($invoiceItems[$item->getId()] / $item->getQtyOrdered());
                        $orderBaseShippingTaxAmount += $vendorOrder->getBaseShippingTaxAmount() *
                            ($invoiceItems[$item->getId()] / $item->getQtyOrdered());
                    }
                    $invoiceQtyVendor = $invoiceItems[$item->getId()];
                    $invoiceQty += $invoiceItems[$item->getId()];
                    $giftwrapperAmount += $item->getGiftwrapperPrice() * $invoiceQtyVendor / $vendorOrderQtyVendor;
                    $discountAmount += $item->getDiscountAmount() * $invoiceQtyVendor / $vendorOrderQtyVendor;
                    $baseDiscountAmount += $item->getBaseDiscountAmount() * $invoiceQtyVendor / $vendorOrderQtyVendor;
                    $baseGiftwrapperAmount += $item->getBaseGiftwrapperPrice() *
                        $invoiceQtyVendor / $vendorOrderQtyVendor;
                    $percent = ($vendorOrder->getTaxAmount() > 0) ?
                        $item->getTaxAmount() / $vendorOrder->getTaxAmount() : 0;
                    $orderTotalTax =  $vendorOrder->getTaxAmount();
                    $taxRate = $orderTotalTax * $percent;
                    $taxAmount = $taxRate * ($invoiceItems[$item->getId()] / $item->getQtyOrdered());

                    $basePercent = ($vendorOrder->getBaseTaxAmount() > 0) ?
                        $item->getBaseTaxAmount() / $vendorOrder->getBaseTaxAmount() : 0;
                    $baseOrderTotalTax =  $vendorOrder->getBaseTaxAmount();
                    $baseTaxRate = $baseOrderTotalTax * $basePercent;
                    $baseTaxAmount = $baseTaxRate * ($invoiceItems[$item->getId()] / $item->getQtyOrdered());
                    $tax += $taxAmount;
                    $baseTax += $baseTaxAmount;

                    $itemTotal = $item->getPriceInclTax() * $invoiceItems[$item->getId()];
                    $itemBaseTotal = $item->getBasePriceInclTax() * $invoiceItems[$item->getId()];
                    $totalToInclude = $itemTotal - $taxAmount;
                    $baseTotalToInclude = $itemBaseTotal - $baseTaxAmount;

                    $invoiceBaseGrandTotal += $baseTotalToInclude;
                    $invoiceGrandTotal += $totalToInclude;
                }
            }
        }

        /* shipping amount reset based on vendor order */
        if ($order->getShippingMethod() != 'rbmatrixrate_rbmatrixrate') {
            $orderShippingAmount = ($vendorOrder->getBaseShippingAmount() * $invoiceQty / $vendorOrderQty);
            $orderBaseShippingAmount = ($vendorOrder->getBaseShippingAmount() * $invoiceQty / $vendorOrderQty);
            $orderShippingTaxAmount = ($vendorOrder->getShippingTaxAmount() * $invoiceQty / $vendorOrderQty);
            $orderBaseShippingTaxAmount = ($vendorOrder->getBaseShippingTaxAmount() * $invoiceQty / $vendorOrderQty);
        }

        $shippingInclTax = ($vendorOrder->getShippingInclTax() * $invoiceQty / $vendorOrderQty);
        $baseShippingInclTax = ($vendorOrder->getBaseShippingInclTax() * $invoiceQty / $vendorOrderQty);

        $discountTaxCompensationAmount = (
            $vendorOrder->getDiscountTaxCompensationAmount() * $invoiceQty / $vendorOrderQty
        );
        $baseDiscountTaxCompensationAmount = (
            $vendorOrder->getBaseDiscountTaxCompensationAmount() * $invoiceQty / $vendorOrderQty
        );
        $shippingDiscountTaxCompensationAmount = (
            $vendorOrder->getShippingDiscountTaxCompensationAmount() * $invoiceQty / $vendorOrderQty
        );
        $baseShippingDiscountTaxCompensationAmount = (
            $vendorOrder->getBaseShippingDiscountTaxCompensationAmnt() * $invoiceQty / $vendorOrderQty
        );
        $shippingDiscountAmount = ($vendorOrder->getShippingDiscountAmount() * $invoiceQty / $vendorOrderQty);
        $baseShippingDiscountAmount = ($vendorOrder->getBaseShippingDiscountAmount() * $invoiceQty / $vendorOrderQty);

        $tax += $shippingDiscountTaxCompensationAmount;
        $baseTax += $baseShippingDiscountTaxCompensationAmount;

        $discountAmount += $shippingDiscountAmount;
        $baseDiscountAmount += $baseShippingDiscountAmount;

        $invoiceGrandTotal -= $discountTaxCompensationAmount;
        $invoiceBaseGrandTotal -= $baseDiscountTaxCompensationAmount;

        /* Add Shipping Tax to tax amount */
        $tax += $orderShippingTaxAmount;
        $baseTax += $orderBaseShippingTaxAmount;
        $finalGrandTotal = $invoiceGrandTotal + $orderShippingAmount + $tax + $giftwrapperAmount +
            $discountTaxCompensationAmount - $discountAmount;
        $finalBaseGrandTotal = $invoiceBaseGrandTotal + $orderShippingAmount + $baseTax + $baseGiftwrapperAmount +
            $baseDiscountTaxCompensationAmount - $baseDiscountAmount;

        $finalGrandTotal = min($finalGrandTotal, $vendorOrder->getGrandTotal());
        $finalBaseGrandTotal = min($finalBaseGrandTotal, $vendorOrder->getBaseGrandTotal());

        $finalGrandTotal = min($finalGrandTotal, ($order->getGrandTotal() - $order->getTotalPaid()));
        $finalBaseGrandTotal = min($finalBaseGrandTotal, ($order->getBaseGrandTotal() - $order->getBaseTotalPaid()));

        $finalSubTotal = min($invoiceGrandTotal, ($order->getSubtotal() - $order->getSubtotalInvoiced()));
        $finalBaseSubTotal = min(
            $invoiceBaseGrandTotal,
            ($order->getBaseSubtotal() - $order->getBaseSubtotalInvoiced())
        );

        $invoice->setGrandTotal($finalGrandTotal);
        /* $vendorOrder->getGrandTotal() * $invoiceQty / $vendorOrderQty); */
        $invoice->setBaseGrandTotal($finalBaseGrandTotal);
        /* $vendorOrder->getBaseGrandTotal() * $invoiceQty / $vendorOrderQty); */
        $invoice->setTaxAmount($tax);
        $invoice->setBaseTaxAmount($baseTax);
        $invoice->setSubtotal($invoiceGrandTotal);
        $invoice->setBaseSubtotal($invoiceBaseGrandTotal);
        $invoice->setShippingAmount($orderShippingAmount);
        $invoice->setShippingTaxAmount($orderShippingTaxAmount);
        $invoice->setBaseShippingAmount($orderBaseShippingAmount);
        $invoice->setBaseShippingTaxAmount($orderBaseShippingTaxAmount);
        $invoice->setShippingInclTax($shippingInclTax);
        $invoice->setShippingInclTax($shippingInclTax);
        $invoice->setGrandTotal($finalGrandTotal);
        $invoice->setBaseGrandTotal($finalBaseGrandTotal);
        $invoice->setDiscountAmount($discountAmount * -1);
        $invoice->setBaseDiscountAmount($baseDiscountAmount * -1);

        return $invoice;
    }
}
