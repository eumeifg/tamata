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

use Magedelight\Sales\Model\Order as VendorOrder;
use Magedelight\Sales\Model\Order\SplitOrder\DiscountProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * @author Rocket Bazaar Core Team
 */
class Save extends \Magedelight\Backend\App\Action
{

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var InvoiceService
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
     * @var DiscountProcessor
     */
    protected $discountProcessor;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $transaction;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    /**
     * Save constructor.
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param Registry $registry
     * @param InvoiceSender $invoiceSender
     * @param ShipmentSender $shipmentSender
     * @param ShipmentFactory $shipmentFactory
     * @param InvoiceService $invoiceService
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magedelight\Sales\Helper\Data $salesHelper
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param DiscountProcessor $discountProcessor
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Sales\Model\Order $order
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        Registry $registry,
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender,
        ShipmentFactory $shipmentFactory,
        InvoiceService $invoiceService,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magedelight\Sales\Helper\Data $salesHelper,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        DiscountProcessor $discountProcessor,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order $order
    ) {
        $this->registry = $registry;
        $this->invoiceSender = $invoiceSender;
        $this->shipmentSender = $shipmentSender;
        $this->shipmentFactory = $shipmentFactory;
        $this->invoiceService = $invoiceService;
        $this->redirect = $redirect;
        $this->salesHelper = $salesHelper;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->discountProcessor = $discountProcessor;
        $this->logger = $logger;
        $this->transaction = $transaction;
        $this->order = $order;
        parent::__construct($context);
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
        }
        return false;
    }

    /**
     * Prepare shipment
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _prepareShipment($invoice)
    {
        $invoiceData = $this->getRequest()->getParam('invoice');

        $shipment = $this->shipmentFactory->create(
            $invoice->getOrder(),
            isset($invoiceData['items']) ? $invoiceData['items'] : [],
            $this->getRequest()->getPost('tracking')
        );

        if (!$shipment->getTotalQty()) {
            return false;
        }

        return $shipment->register();
    }

    /**
     * Save invoice
     * We can save only new invoice. Existing invoices are not editable
     *
     * @return \Magento\Framework\Controller\ResultInterface
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_isAllowed()) {
            $this->messageManager->addErrorMessage(
                __('You are not allowed to use requested feature, Please contact Marketplace Admin in case any query.')
            );
            return $resultRedirect->setPath('rbsales/order/index', ['tab' => $this->getTab()]);
        }
        $isPost = $this->getRequest()->isPost();
        if (!$isPost) {
            $this->messageManager->addErrorMessage(__('We can\'t save the invoice right now.'));
            return $resultRedirect->setPath('rbsales/order/index', ['tab' => $this->getTab()]);
        }

        try {
            $orderIds = $this->getRequest()->getParam('order_id');
            if (!empty($this->getRequest()->getPost('invoice'))) {
                $vorderId = $this->generateInvoice($orderIds);
                return $resultRedirect->setPath(
                    'rbsales/order/view',
                    ['id' => $vorderId, 'tab' => $this->getTab()]
                );
            } else {
                foreach ($orderIds as $orderId) {
                    $this->generateInvoice($orderId);
                }
                return $resultRedirect->setPath(
                    'rbsales/order/index',
                    ['sfrm' => 'confirmed', 'tab' => $this->getTab()]
                );
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t save the invoice right now.'));
            $this->logger->critical($e);
        }
    }

    /**
     *
     * @param $orderId
     * @return
     */
    protected function generateInvoice($orderId)
    {
        $isMassAction = strpos($this->redirect->getRefererUrl(), 'order/index') ? true : false;
        $data = $this->getRequest()->getPost('invoice');
        $liableEntitiesForDiscount = [];

        if (!empty($data['comment_text'])) {
            $this->_session->setCommentText($data['comment_text']);
        }

        try {
            $invoiceData = $this->getRequest()->getParam('invoice', []);
            $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->order->load($orderId);
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
            }
            $vendorId = $this->_auth->getUser()->getVendorId();
            $vendorOrder = $this->vendorOrderRepository->getById($this->getRequest()->getParam('vendor_order_id'));

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
                if ($item->getData('vendor_order_id') != $this->getRequest()->getParam('vendor_order_id')) {
                    $invoiceItems[$item->getId()] = 0;
                } elseif (intval($item->getQtyCanceled()) == '0') {
                    $vendorOrderQtyVendor = $item->getQtyOrdered();
                    $vendorOrderQty += $item->getQtyOrdered();
                    if (!isset($invoiceItems[$item->getId()])) {
                        $invoiceItems[$item->getId()] = 0;
                    }
                    if ($isMassAction) {
                        $invoiceItems[$item->getId()] = $item->getQtyOrdered() - $item->getQtyInvoiced();
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
                $liableEntitiesForDiscount[$item->getVendorOrderId()] =
                    $this->discountProcessor->calculateVendorDiscountAmount($item, $item->getVendorId());
            }
            $invoice = $this->invoiceService->prepareInvoice(
                $order,
                $invoiceItems
            );
            $invoice->setVendorOrder($vendorOrder);
            $invoice->setVendorOrderId($vendorOrder->getVendorOrderId());
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

            /*$tax = min(round($tax, 2), (floor((($finalGrandTotal -
             $finalSubTotal)-$orderShippingAmount)  * 100) / 100 ));
            $baseTax = min(round($baseTax, 2), (floor((($finalBaseGrandTotal -
             $finalBaseSubTotal)-$orderBaseShippingAmount) * 100) / 100 ));*/

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

            if (!$invoice) {
                throw new LocalizedException(__('We can\'t save the invoice right now.'));
            }

            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }

            $this->registry->unregister('current_invoice');
            $this->registry->register('current_invoice', $invoice);
            if (!empty($data['capture_case'])) {
                $invoice->setRequestedCaptureCase($data['capture_case']);
            }

            if (!empty($data['comment_text'])) {
                $invoice->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );

                $invoice->setCustomerNote($data['comment_text']);
                $invoice->setCustomerNoteNotify(isset($data['comment_customer_notify']));
            }

            $invoice->register();

            $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $invoice->getOrder()->setIsInProcess(true);

            $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
            $shipment = false;
            if (!empty($data['do_shipment']) || (int)$invoice->getOrder()->getForcedShipmentWithInvoice()) {
                $shipment = $this->_prepareShipment($invoice);
                if ($shipment) {
                    $transactionSave->addObject($shipment);
                }
            }
            if ($vendorOrder->getId()) {
                $liableEntityForDiscount = VendorOrder::LIABLE_ENTITY_FOR_DISCOUNT_ADMIN;
                if (!empty($liableEntitiesForDiscount) &&
                    array_key_exists($vendorOrder->getId(), $liableEntitiesForDiscount)) {
                    $liableEntityForDiscount = ($liableEntitiesForDiscount[$vendorOrder->getId()]) ?
                        VendorOrder::LIABLE_ENTITY_FOR_DISCOUNT_SELLER : VendorOrder::LIABLE_ENTITY_FOR_DISCOUNT_ADMIN;
                }
                $vendorOrder->registerInvoice($invoice, $liableEntityForDiscount);
                $vendorOrder->setData('main_order', $invoice->getOrder());
                $transactionSave->addObject($vendorOrder);
            }
            $transactionSave->save();

            if (isset($shippingResponse) && $shippingResponse->hasErrors()) {
                $this->messageManager->addErrorMessage(
                    __(
                        'The invoice and the shipment  have been created. ' .
                        'The shipping label cannot be created now.'
                    )
                );
            } elseif (!empty($data['do_shipment'])) {
                $this->messageManager->addSuccessMessage(__('You created the invoice and shipment.'));
            } else {
                $this->messageManager->addSuccessMessage(__('The invoice has been created.'));
            }

            // send invoice/shipment emails
            try {
                if (!empty($data['send_email'])) {
                    $this->invoiceSender->send($invoice);
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('We can\'t send the invoice email right now.'));
            }
            if ($shipment) {
                try {
                    if (!empty($data['send_email'])) {
                        $this->shipmentSender->send($shipment);
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    $this->messageManager->addErrorMessage(__('We can\'t send the shipment right now.'));
                }
            }
            $this->_eventManager->dispatch('vendor_order_invoice_generate_after', ['order' => $vendorOrder]);

            $this->_session->getCommentText(true);
            return $vendorOrder->getId();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t save the invoice right now.'));
            $this->logger->critical($e);
        }
    }
}
