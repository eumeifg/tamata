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
namespace Magedelight\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magedelight\Sales\Model\Sales\Service\InvoiceService;
use Magedelight\Sales\Api\OrderRepositoryInterface;
use Magedelight\Sales\Model\Order\SplitOrder\DiscountProcessor;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Store\Model\ScopeInterface;
use Magedelight\Commissions\Model\Source\Actor;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Backend\App\Action
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
     * @var OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * @var DiscountProcessor
     */
    protected $discountProcessor;

    /**
     * @var \Magedelight\Sales\Helper\Data
     */
    protected $salesHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $transaction;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param Registry $registry
     * @param InvoiceSender $invoiceSender
     * @param ShipmentSender $shipmentSender
     * @param ShipmentFactory $shipmentFactory
     * @param InvoiceService $invoiceService
     * @param OrderRepositoryInterface $vendorOrderRepository
     * @param DiscountProcessor $discountProcessor
     * @param \Magedelight\Sales\Helper\Data $salesHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\DB\Transaction $transaction
     */
    public function __construct(
        Action\Context $context,
        Registry $registry,
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender,
        ShipmentFactory $shipmentFactory,
        InvoiceService $invoiceService,
        OrderRepositoryInterface $vendorOrderRepository,
        DiscountProcessor $discountProcessor,
        \Magedelight\Sales\Helper\Data $salesHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\DB\Transaction $transaction
    ) {
        $this->registry = $registry;
        $this->invoiceSender = $invoiceSender;
        $this->shipmentSender = $shipmentSender;
        $this->shipmentFactory = $shipmentFactory;
        $this->invoiceService = $invoiceService;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->discountProcessor = $discountProcessor;
        $this->salesHelper = $salesHelper;
        $this->logger = $logger;
        $this->order = $order;
        $this->transaction = $transaction;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_invoice');
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
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        $liableEntitiesForDiscount = [];

        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addErrorMessage(__('We can\'t save the invoice right now.'));
            return $resultRedirect->setPath('sales/order/index');
        }

        $data = $this->getRequest()->getPost('invoice');
        $orderId = $this->getRequest()->getParam('order_id');

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
            $vendorId = $this->getRequest()->getParam('do_as_vendor');
            $vendorOrder = $this->vendorOrderRepository->getById(
                $this->getRequest()->getParam('vendor_order_id')
            );

            if (!$vendorOrder->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }

            foreach ($vendorOrder->getItems() as $item) {
                $liableEntitiesForDiscount[$item->getVendorOrderId()] =
                    $this->discountProcessor->calculateVendorDiscountAmount($item, $item->getVendorId());
            }

            $invoice = $this->invoiceService->prepareInvoice(
                $order,
                $invoiceItems,
                $vendorOrder->getVendorOrderId()
            );

            $invoice = $this->invoiceService->processInvoiceData(
                $invoice,
                $order,
                $invoiceItems,
                $vendorOrder,
                $this->salesHelper->getConfig('commission/payout/shipping_liability', ScopeInterface::SCOPE_WEBSITE)
            );

            if (!$invoice) {
                throw new LocalizedException(__('We can\'t save the invoice right now.'));
            }

            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
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

            /* send invoice/shipment emails*/
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
            return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t save the invoice right now.'));
            $this->logger->critical($e);
        }
        return $resultRedirect->setPath(
            'rbsales/*/new',
            [
                'order_id' => $orderId,
                'do_as_vendor' => $vendorId,
                'vendor_order_id' => $this->getRequest()->getParam('vendor_order_id')
            ]
        );
    }
}
