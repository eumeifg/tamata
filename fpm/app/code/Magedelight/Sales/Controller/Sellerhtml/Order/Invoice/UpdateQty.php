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
use Magedelight\Sales\Model\Sales\Service\InvoiceService;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateQty extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    private $design;

    /**
     * @var Registry
     */
    private $registry;

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
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    /**
     * UpdateQty constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param RawFactory $resultRawFactory
     * @param Registry $registry
     * @param \Magedelight\Vendor\Model\Design $design
     * @param InvoiceService $invoiceService
     * @param \Magedelight\Sales\Model\Order\Invoice $rbInvoiceModel
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param \Magento\Sales\Model\Order $order
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory,
        Registry $registry,
        \Magedelight\Vendor\Model\Design $design,
        InvoiceService $invoiceService,
        \Magedelight\Sales\Model\Order\Invoice $rbInvoiceModel,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Magento\Sales\Model\Order $order
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->invoiceService = $invoiceService;
        $this->registry = $registry;
        $this->design = $design;
        $this->rbInvoiceModel = $rbInvoiceModel;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->order = $order;
        parent::__construct($context);
    }

    /**
     * Update items qty action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->design->applyVendorDesign();
        try {
            $orderId = $this->getRequest()->getParam('order_id');
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
            $invoice = $this->invoiceService->prepareInvoice(
                $order,
                $invoiceItems,
                $this->getRequest()->getParam('vendor_order_id')
            );
            $invoice->setVendorId($vendorId);
            $invoice->setVendorOrder($vendorOrder);

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
            $invoice = $this->rbInvoiceModel->processInvoice($order, $invoice, $vendorOrder, $vendorId, $invoiceItems);

            $this->_eventManager->dispatch('md_vendor_order_invoice_register_update', ['invoice' => $invoice]);
            $this->registry->register('current_invoice', $invoice);
            // Save invoice comment text in current invoice object in order to display it in corresponding view
            $invoiceRawCommentText = $invoiceData['comment_text'];
            $invoice->setCommentText($invoiceRawCommentText);

            /** @var \Magento\Framework\View\Result\Page $resultPage */
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

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
    }
}
