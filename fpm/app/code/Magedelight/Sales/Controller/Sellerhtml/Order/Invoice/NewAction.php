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
namespace Magedelight\Sales\Controller\Sellerhtml\Order\Invoice;

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * @author Rocket Bazaar Core Team
 */
class NewAction extends \Magedelight\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @var \Magedelight\Sales\Model\Sales\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magedelight\Sales\Helper\Data
     */
    protected $salesHelper;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * NewAction constructor.
     * @param Context $context
     * @param \Magedelight\Sales\Model\Sales\Service\InvoiceService $invoiceService
     * @param Registry $registry
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $resultPageFactory
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param \Magedelight\Sales\Helper\Data $salesHelper
     */
    public function __construct(
        Context $context,
        \Magedelight\Sales\Model\Sales\Service\InvoiceService $invoiceService,
        Registry $registry,
        \Magedelight\Vendor\Model\Design $design,
        PageFactory $resultPageFactory,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Magedelight\Sales\Helper\Data $salesHelper
    ) {
        $this->design = $design;
        $this->registry = $registry;
        $this->invoiceService = $invoiceService;
        $this->resultPageFactory = $resultPageFactory;
        $this->salesHelper = $salesHelper;
        $this->vendorOrderRepository = $vendorOrderRepository;
        parent::__construct($context);
    }

    /**
     * Redirect to order view page
     *
     * @param $vendorOrder
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _redirectToOrder($vendorOrder)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(
            'rbsales/order/view',
            [
                'vendor_order_id' => $vendorOrder->getVendorOrderId(),
                'id' => $vendorOrder->getOrderId(),
                'tab' => $this->getTab()
            ]
        );
        return $resultRedirect;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        /**
         * @todo authorization according to vendor roles and user management
         */
        if ($this->_authorization->isAllowed('Magedelight_Sales::manage_orders')) {
            return $this->salesHelper->canGenerateInvoice();
        } else {
            return false;
        }
    }

    /**
     * Shipment create page
     *
     * @return \Magento\Framework\Controller\Result\Redirect|PageFactory
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_isAllowed()) {
            $this->messageManager->addError(
                __('You are not allowed to use requested feature, Please contact Marketplace Admin in case any query.')
            );
            return $resultRedirect->setPath('rbsales/order/index', ['tab' => $this->getTab()]);
        }
        $this->design->applyVendorDesign();
        $orderId = $this->getRequest()->getParam('order_id');
        $invoiceData = $this->getRequest()->getParam('invoice', []);
        $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
            }
            $vendorId = $this->_auth->getUser()->getVendorId();
            $vendorOrder = $this->getVendorOrder();
            if (!$vendorOrder->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }

            $vendorOrderQty = $invoiceQty = $orderShippingAmount = $orderShippingTaxAmount = 0;
            $orderBaseShippingAmount = $orderBaseShippingTaxAmount = $giftwrapperAmount = $baseGiftwrapperAmount = 0;
            $discountAmount = $baseDiscountAmount = $storeCreditAmount = $baseStoreCreditAmount = 0;
            $invoiceBaseGrandTotal = $invoiceGrandTotal = $tax = $baseTax = 0;
            foreach ($order->getItemsCollection() as $item) {
                if (($item->getData('vendor_order_id') == $this->getRequest()->getParam('vendor_order_id')) &&
                    (intval($item->getQtyCanceled()) == '0')) {
                    $invoiceItems[$item->getId()] = $item->getQtyToInvoice();

                    $vendorOrderQtyVendor = $item->getQtyOrdered();
                    $vendorOrderQty = $item->getQtyOrdered();  /*$vendorOrderQty += $item->getQtyOrdered(); */
                    if (!isset($invoiceItems[$item->getId()])) {
                        $invoiceItems[$item->getId()] = 0;
                    }
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
                        $baseDiscountAmount += $item->getBaseDiscountAmount() *
                            $invoiceQtyVendor / $vendorOrderQtyVendor;
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
            $invoice = $this->invoiceService->prepareInvoice(
                $order,
                $invoiceItems,
                $this->getRequest()->getParam('vendor_order_id'),
                true
            );
            $invoice->setVendorId($vendorId);
            $invoice->setVendorOrder($vendorOrder);

            /* shipping amount reset based on vendor order */
            if ($order->getShippingMethod() != 'rbmatrixrate_rbmatrixrate') {
                $orderShippingAmount = ($vendorOrder->getShippingAmount() * $invoiceQty / $vendorOrderQty);
                $orderBaseShippingAmount = ($vendorOrder->getBaseShippingAmount() * $invoiceQty / $vendorOrderQty);
                $orderShippingTaxAmount = ($vendorOrder->getShippingTaxAmount() * $invoiceQty / $vendorOrderQty);
                $orderBaseShippingTaxAmount =
                    ($vendorOrder->getBaseShippingTaxAmount() * $invoiceQty / $vendorOrderQty);
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
            $baseShippingDiscountAmount =
                ($vendorOrder->getBaseShippingDiscountAmount() * $invoiceQty / $vendorOrderQty);

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
            $finalBaseGrandTotal = min(
                $finalBaseGrandTotal,
                ($order->getBaseGrandTotal() - $order->getBaseTotalPaid())
            );

            $finalSubTotal = min($invoiceGrandTotal, ($order->getSubtotal() - $order->getSubtotalInvoiced()));
            $finalBaseSubTotal = min(
                $invoiceBaseGrandTotal,
                ($order->getBaseSubtotal() - $order->getBaseSubtotalInvoiced())
            );

            /* $vendorOrder->getGrandTotal() * $invoiceQty / $vendorOrderQty); */
            $invoice->setGrandTotal($finalGrandTotal);
            /* $vendorOrder->getBaseGrandTotal() * $invoiceQty / $vendorOrderQty); */
            $invoice->setBaseGrandTotal($finalBaseGrandTotal);
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

            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
            $this->_eventManager->dispatch('md_vendor_order_invoice_register', ['invoice' => $invoice]);

            $this->registry->register('current_invoice', $invoice);

            $comment = $this->_session->getCommentText(true);
            if ($comment) {
                $invoice->setCommentText($comment);
            }

            /** @var \Magento\Framework\View\Result\PageFactory $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Invoice'));
            return $resultPage;
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $this->_redirectToOrder($vendorOrder);
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, 'Cannot create an invoice.');
            return $this->_redirectToOrder($vendorOrder);
        }
    }

    /**
     * @return bool|\Magedelight\Sales\Api\Data\VendorOrderInterface
     */
    protected function getVendorOrder()
    {
        if (!($vendorId = $this->_auth->getUser()->getVendorId())) {
            return false;
        }
        $orderId = $this->getRequest()->getParam('order_id', false);

        $vendorOrder = $this->vendorOrderRepository
            ->getById($this->getRequest()->getParam('vendor_order_id', false));
        $this->registry->register('vendor_order', $vendorOrder);

        return $vendorOrder;
    }
}
