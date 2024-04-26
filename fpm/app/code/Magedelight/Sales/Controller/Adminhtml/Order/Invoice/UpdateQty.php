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

use Magedelight\Commissions\Model\Source\Actor;
use Magedelight\Sales\Model\Sales\Service\InvoiceService;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateQty extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\View
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * @var \Magedelight\Sales\Helper\Data
     */
    protected $salesHelper;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    /**
     * UpdateQty constructor.
     * @param Context $context
     * @param Registry $registry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param RawFactory $resultRawFactory
     * @param InvoiceService $invoiceService
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param \Magedelight\Sales\Helper\Data $salesHelper
     * @param \Magento\Sales\Model\Order $order
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory,
        InvoiceService $invoiceService,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Magedelight\Sales\Helper\Data $salesHelper,
        \Magento\Sales\Model\Order $order
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->invoiceService = $invoiceService;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->salesHelper = $salesHelper;
        $this->order = $order;
        parent::__construct($context, $registry, $resultForwardFactory);
    }

    /**
     * Update items qty action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $orderId = $this->getRequest()->getParam('order_id');
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
            $invoice = $this->invoiceService->prepareInvoice(
                $order,
                $invoiceItems,
                $this->getRequest()->getParam('vendor_order_id')
            );
            $invoice->setVendorId($vendorId);
            $invoice->setVendorOrder($vendorOrder);
            /**
             * @todo Shipping amount hack in case of update Qty.
             * currently we have devided item wised equally
             */
            $vendorOrderQty = 0;
            $vendorInvoicedQty = 0;
            $invoiceShipping = 0;
            foreach ($vendorOrder->getItems() as $orderItem) {
                $vendorOrderQty += $orderItem->getQtyOrdered();
                $vendorInvoicedQty += $orderItem->getQtyInvoiced();
                if (array_key_exists($orderItem->getId(), $invoiceItems) &&
                    $order->getShippingMethod() == 'rbmatrixrate_rbmatrixrate') {
                    $invoiceShipping += ($invoiceItems[$orderItem->getId()] *
                            $orderItem->getShippingAmount()) / $orderItem->getQtyOrdered();
                }
            }
            if ($vendorOrder->getShippingMethod() != 'rbmatrixrate_rbmatrixrate' &&
                substr($vendorOrder->getShippingMethod(), 0, 18) != 'rbmultipleshipping') {
                if ($this->salesHelper->getConfig(
                    'commission/payout/shipping_liability',
                    ScopeInterface::SCOPE_WEBSITE
                ) == Actor::ADMIN) {
                    if ($order->getShippingAmount() > $order->getShippingInvoiced()) {
                        $invoiceShipping = $order->getShippingAmount() - $order->getShippingInvoiced();
                    }
                } else {
                    $invoiceShipping = $vendorOrder->getShippingAmount() * $invoice->getTotalQty() / $vendorOrderQty;
                }
            }
            $invoice->setGrandTotal($invoice->getGrandTotal() - $invoice->getShippingAmount() + $invoiceShipping);

            if ($invoice->getTotalQty() == ($vendorOrderQty + $vendorInvoicedQty)) {
                $invoice->setGrandTotal($vendorOrder->getGrandTotal() - $vendorOrder->getTotalInvoiced());
                $invoice->setSubtotal($vendorOrder->getSubtotal() - $vendorOrder->getSubtotalInvoiced());
                $invoice->setSubtotalInclTax(
                    $vendorOrder->getSubtotalInclTax() - $vendorOrder->getSubtotalInvoiced()
                );
            } elseif (substr($vendorOrder->getShippingMethod(), 0, 18) != 'rbmultipleshipping') {
                $invoice->setBaseGrandTotal(
                    $invoice->getBaseGrandTotal() - $invoice->getBaseShippingAmount() + $invoiceShipping
                );
            }
            if (substr($vendorOrder->getShippingMethod(), 0, 18) != 'rbmultipleshipping') {
                $invoice->setShippingAmount($invoiceShipping);
            }
            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
            if ((!empty($invoiceItems) && min($invoiceItems) < 0) ||
                (is_numeric($invoice->getTotalQty()) && floor($invoice->getTotalQty()) != $invoice->getTotalQty())) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Negative or decimal quantities are not allowed for invoice.')
                );
            }
            $this->_eventManager->dispatch('md_vendor_order_invoice_register_update', ['invoice' => $invoice]);
            $this->registry->register('current_invoice', $invoice);
            // Save invoice comment text in current invoice object in order to display it in corresponding view
            $invoiceRawCommentText = $invoiceData['comment_text'];
            $invoice->setCommentText($invoiceRawCommentText);

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
            $response = $resultPage->getLayout()->getBlock('order_items')->toHtml();
        } catch (LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot update item quantity.')];
        }
        if (is_array($response)) {
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($response);
            return $resultJson;
        } else {
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setContents($response);
            return $resultRaw;
        }
    }
}
