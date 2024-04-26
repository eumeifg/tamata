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
namespace Magedelight\Sales\Model\Sales\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magedelight\Commissions\Model\Source\Actor;
use Magento\Sales\Api\Data\OrderItemInterface;

class InvoiceService extends \Magento\Sales\Model\Service\InvoiceService
{
    /**
     * @param Order $order
     * @param array $qtys
     * @param null $vendorOrderId
     * @param bool $discountFlag
     * @return Order\Invoice
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareInvoice(
        Order $order,
        array $orderItemsQtyToInvoice = [],
        $vendorOrderId = null,
        $discountFlag = false
    ): InvoiceInterface {
        $invoice = $this->orderConverter->toInvoice($order);
        $totalQty = 0;
        $preparedItemsQty = $this->prepareItemsQty($order, $orderItemsQtyToInvoice);
        foreach ($order->getAllItems() as $orderItem) {
            if (!$this->canInvoiceItem($orderItem, $preparedItemsQty) || $orderItem->getData('vendor_order_id') != $vendorOrderId) {
                continue;
            }
            if ($discountFlag) {
                $orderItem->setDiscountFlag(true);
            }
            $item = $this->orderConverter->itemToInvoiceItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered() ? $orderItem->getQtyOrdered() : 1;
            } elseif (isset($orderItemsQtyToInvoice[$orderItem->getId()])) {
                $qty = (double) $orderItemsQtyToInvoice[$orderItem->getId()];
            } else {
                $qty = $orderItem->getQtyToInvoice();
            }
            $totalQty += $qty;
            $this->setInvoiceItemQuantity($item, $qty);
            $invoice->addItem($item);
        }
        $invoice->setTotalQty($totalQty);
        $invoice->collectTotals();
        $order->getInvoiceCollection()->addItem($invoice);
        return $invoice;
    }

    /**
     * Prepare qty to invoice for parent and child products if theirs qty is not specified in initial request.
     *
     * @param Order $order
     * @param array $orderItemsQtyToInvoice
     * @return array
     */
    private function prepareItemsQty(
        Order $order,
        array $orderItemsQtyToInvoice
    ): array {
        foreach ($order->getAllItems() as $orderItem) {
            if (isset($orderItemsQtyToInvoice[$orderItem->getId()])) {
                if ($orderItem->isDummy() && $orderItem->getHasChildren()) {
                    $orderItemsQtyToInvoice = $this->setChildItemsQtyToInvoice($orderItem, $orderItemsQtyToInvoice);
                }
            } else {
                if (isset($orderItemsQtyToInvoice[$orderItem->getParentItemId()])) {
                    $orderItemsQtyToInvoice[$orderItem->getId()] =
                        $orderItemsQtyToInvoice[$orderItem->getParentItemId()];
                }
            }
        }

        return $orderItemsQtyToInvoice;
    }

    /**
     * @param $invoice
     * @param $order
     * @param $invoiceItems
     * @param $vendorOrder
     * @param $shippingLiableActor
     * @return Order\Invoice
     */
    public function processInvoiceData(
        $invoice,
        $order,
        $invoiceItems,
        $vendorOrder,
        $shippingLiableActor
    ) {
        $vendorOrderQty = $invoiceQty = $orderShippingAmount = $orderShippingTaxAmount = 0;
        $orderBaseShippingAmount = $orderBaseShippingTaxAmount = $giftwrapperAmount = 0;
        $baseGiftwrapperAmount = $discountAmount = $baseDiscountAmount = $storeCreditAmount = 0;

      /* On creat new credit memo check available customer store credit to be refund  */
        $storeCreditAmount = $invoice->getBaseCustomerBalanceAmount();  
        
        $creditHistoryModel = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get(\Magedelight\Sales\Model\CheckStoreCreditHistory::class);

        $creditAction = '4,5'; // 5 = reverted, 4 = refunded
        $customerRevertedAmount = 0;
        $creditReverted  = $creditHistoryModel->getOrderRevertRefundHistory($order, $creditAction);
        if($creditReverted['reverted'] && $creditReverted['reverted'] > 0 ){
            $customerRevertedAmount = $creditReverted['reverted']; 
        }    
        if($customerRevertedAmount >= $storeCreditAmount ){
            $storeCreditAmount = 0;
        }
        
        /*On creat new credit memo check available customer store credit to be refund*/

        $invoiceBaseGrandTotal = $invoiceGrandTotal = $tax = $baseTax = 0;
        foreach ($vendorOrder->getItems() as $item) {
            $vendorOrderQtyVendor = $item->getQtyOrdered();
            $vendorOrderQty = $item->getQtyOrdered(); /*$vendorOrderQty += $item->getQtyOrdered(); */
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
                $baseGiftwrapperAmount += $item->getBaseGiftwrapperPrice() * $invoiceQtyVendor / $vendorOrderQtyVendor;
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

        $invoice->setVendorId($vendorOrder->getVendorId());
        $invoice->setVendorOrderId($vendorOrder->getVendorOrderId());
        $invoice->setVendorOrder($vendorOrder);

        /* shipping amount reset based on vendor order */
        if ($order->getShippingMethod() != 'rbmatrixrate_rbmatrixrate') {
            if ($shippingLiableActor == Actor::ADMIN) {
                if ($order->getShippingAmount() > $order->getShippingInvoiced()) {
                    $orderShippingAmount = $order->getShippingAmount() - $order->getShippingInvoiced();
                    $orderBaseShippingAmount = $order->getBaseShippingAmount() - $order->getBaseShippingInvoiced();
                    $orderShippingTaxAmount = $order->getShippingTaxAmount();
                    $orderBaseShippingTaxAmount = $order->getBaseShippingTaxAmount();
                }
            } else {
                $orderShippingAmount = ($vendorOrder->getShippingAmount() * $invoiceQty / $vendorOrderQty);
                $orderBaseShippingAmount = ($vendorOrder->getBaseShippingAmount() * $invoiceQty / $vendorOrderQty);
                $orderShippingTaxAmount = ($vendorOrder->getShippingTaxAmount() * $invoiceQty / $vendorOrderQty);
                $orderBaseShippingTaxAmount =
                    ($vendorOrder->getBaseShippingTaxAmount() * $invoiceQty / $vendorOrderQty);
            }
        }

        $shippingInclTax = ($vendorOrder->getShippingInclTax() * $invoiceQty / $vendorOrderQty);
        $baseShippingInclTax = ($vendorOrder->getBaseShippingInclTax() * $invoiceQty / $vendorOrderQty);

        $discountTaxCompensationAmount =
            ($vendorOrder->getDiscountTaxCompensationAmount() * $invoiceQty / $vendorOrderQty);
        $baseDiscountTaxCompensationAmount =
            ($vendorOrder->getBaseDiscountTaxCompensationAmount() * $invoiceQty / $vendorOrderQty);
        $shippingDiscountTaxCompensationAmount =
            ($vendorOrder->getShippingDiscountTaxCompensationAmount() * $invoiceQty / $vendorOrderQty);
        $baseShippingDiscountTaxCompensationAmount =
            ($vendorOrder->getBaseShippingDiscountTaxCompensationAmnt() * $invoiceQty / $vendorOrderQty);
        $shippingDiscountAmount = ($vendorOrder->getShippingDiscountAmount() * $invoiceQty / $vendorOrderQty);
        $baseShippingDiscountAmount = ($vendorOrder->getBaseShippingDiscountAmount() * $invoiceQty / $vendorOrderQty);

        $tax += $shippingDiscountTaxCompensationAmount;
        $baseTax += $baseShippingDiscountTaxCompensationAmount;

        $discountAmount += $shippingDiscountAmount;
        $baseDiscountAmount += $baseShippingDiscountAmount;

        $invoiceGrandTotal -= $discountTaxCompensationAmount;
        $invoiceBaseGrandTotal -= $baseDiscountTaxCompensationAmount;

        /*if Store credit amount grater than the item total set store credit equals to that only*/
        if($storeCreditAmount > $invoiceGrandTotal){

            $storeCreditAmount = $invoiceGrandTotal;
        }
        /*if Store credit amount grater than the item total set store credit equals to that only*/

        /* Add Shipping Tax to tax amount */
        $tax += $orderShippingTaxAmount;
        $baseTax += $orderBaseShippingTaxAmount;
        $finalGrandTotal = $invoiceGrandTotal + $orderShippingAmount + $tax + $giftwrapperAmount +
            $discountTaxCompensationAmount - $discountAmount - $storeCreditAmount;
        $finalBaseGrandTotal = $invoiceBaseGrandTotal + $orderShippingAmount + $baseTax + $baseGiftwrapperAmount +
            $baseDiscountTaxCompensationAmount - $baseDiscountAmount;

        if ($shippingLiableActor == Actor::SELLER) {
            $finalGrandTotal = min($finalGrandTotal, $vendorOrder->getGrandTotal());
            $finalBaseGrandTotal = min($finalBaseGrandTotal, $vendorOrder->getBaseGrandTotal());
        }

        $finalGrandTotal = min($finalGrandTotal, ($order->getGrandTotal() - $order->getTotalPaid()));
        $finalBaseGrandTotal = min($finalBaseGrandTotal, ($order->getBaseGrandTotal() - $order->getBaseTotalPaid()));

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
        $invoice->setBaseShippingInclTax($baseShippingInclTax);
        $invoice->setGrandTotal($finalGrandTotal);
        $invoice->setBaseGrandTotal($finalBaseGrandTotal);
        $invoice->setDiscountAmount($discountAmount * -1);
        $invoice->setBaseDiscountAmount($baseDiscountAmount * -1);
        $invoice->setBaseDiscountTaxCompensationAmount($baseDiscountTaxCompensationAmount);
        $invoice->setDiscountTaxCompensationAmount($discountTaxCompensationAmount);
        $invoice->setShippingDiscountTaxCompensationAmount($shippingDiscountTaxCompensationAmount);
        $invoice->setBaseShippingDiscountTaxCompensationAmnt($baseShippingDiscountTaxCompensationAmount);

        return $invoice;
    }

    /**
     * Set quantity to invoice item.
     *
     * @param InvoiceItemInterface $item
     * @param float $qty
     * @return InvoiceManagementInterface
     * @throws LocalizedException
     */
    private function setInvoiceItemQuantity(InvoiceItemInterface $item, float $qty): InvoiceManagementInterface
    {
        $qty = ($item->getOrderItem()->getIsQtyDecimal()) ? (double) $qty : (int) $qty;
        $qty = $qty > 0 ? $qty : 0;

        /**
         * Check qty availability
         */
        $qtyToInvoice = sprintf("%F", $item->getOrderItem()->getQtyToInvoice());
        $qty = sprintf("%F", $qty);
        if ($qty > $qtyToInvoice && !$item->getOrderItem()->isDummy()) {
            throw new LocalizedException(
                __('We found an invalid quantity to invoice item "%1".', $item->getName())
            );
        }

        $item->setQty($qty);

        return $this;
    }

    /**
     * Check if order item can be invoiced.
     *
     * @param OrderItemInterface $item
     * @param array $qtys
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function canInvoiceItem(OrderItemInterface $item, array $qtys): bool
    {
        if ($item->getLockedDoInvoice()) {
            return false;
        }
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    if (empty($qtys)) {
                        if ($child->getQtyToInvoice() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $parent->getQtyToInvoice() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $item->getQtyToInvoice() > 0;
        }
    }
}
