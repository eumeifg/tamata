<?php

namespace MDC\Sales\Model\Sales\Service;

use Magedelight\Commissions\Model\Source\Actor;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Sales\Model\Order;

class InvoiceService extends \Magedelight\Sales\Model\Sales\Service\InvoiceService
{
    public function __construct(
        \Magento\Sales\Api\InvoiceRepositoryInterface $repository,
        \Magento\Sales\Api\InvoiceCommentRepositoryInterface $commentRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        Order\InvoiceNotifier $notifier,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Convert\Order $orderConverter,
        JsonSerializer $serializer
    ) {
        parent::__construct($repository, $commentRepository, $criteriaBuilder, $filterBuilder, $notifier, $orderRepository, $orderConverter, $serializer);
    }

    public function processInvoiceData($invoice, $order, $invoiceItems, $vendorOrder, $shippingLiableActor)
    {
        $vendorOrderQty = $invoiceQty = $orderShippingAmount = $orderShippingTaxAmount = 0;
        $orderBaseShippingAmount = $orderBaseShippingTaxAmount = $giftwrapperAmount = 0;
        $baseGiftwrapperAmount = $discountAmount = $baseDiscountAmount = $storeCreditAmount = 0;

        /* On creat new credit memo check available customer store credit to be refund  */
        $storeCreditAmount = $invoice->getBaseCustomerBalanceAmount();

        $creditHistoryModel = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magedelight\Sales\Model\CheckStoreCreditHistory::class);

        $creditAction = '4,5'; // 5 = reverted, 4 = refunded
        $customerRevertedAmount = 0;
        /*$creditReverted  = $creditHistoryModel->getOrderRevertRefundHistory($order, $creditAction);
        if($creditReverted['reverted'] && $creditReverted['reverted'] > 0 ){
            $customerRevertedAmount = $creditReverted['reverted'];
        }*/
        if($customerRevertedAmount >= $storeCreditAmount ){
            $storeCreditAmount = 0;
        }

        //echo "<pre>"; print_r($storeCreditAmount); echo "</pre>"; die();

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
            $baseDiscountTaxCompensationAmount - $baseDiscountAmount - $storeCreditAmount;

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
        $invoice->setBaseCustomerBalanceAmount($storeCreditAmount);
        $invoice->setCustomerBalanceAmount($storeCreditAmount);
        return $invoice;
    }
}