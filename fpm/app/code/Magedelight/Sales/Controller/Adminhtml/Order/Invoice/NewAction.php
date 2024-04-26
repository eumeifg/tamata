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

use Magedelight\Sales\Model\Sales\Service\InvoiceService;
use Magento\Backend\App\Action;
use Magento\Store\Model\ScopeInterface;

class NewAction extends \Magento\Sales\Controller\Adminhtml\Order
{

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
     * NewAction constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param InvoiceService $invoiceService
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param \Magedelight\Sales\Helper\Data $salesHelper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $logger,
        InvoiceService $invoiceService,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Magedelight\Sales\Helper\Data $salesHelper
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );
        $this->invoiceService = $invoiceService;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->salesHelper = $salesHelper;
    }

    /**
     * Invoice create page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $invoiceData = $this->getRequest()->getParam('invoice', []);
        $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->_initOrder();
            $vendorOrder = $this->vendorOrderRepository->getById(
                $this->getRequest()->getParam('vendor_order_id')
            );

            if (!$vendorOrder->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }

            foreach ($vendorOrder->getItems() as $item) {
                $invoiceItems[$item->getId()] = $item->getQtyToInvoice();
                if (!isset($invoiceItems[$item->getId()])) {
                    $invoiceItems[$item->getId()] = 0;
                }
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

            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
            $this->_eventManager->dispatch('md_vendor_order_invoice_register', ['invoice' => $invoice]);

            $this->_coreRegistry->register('current_invoice', $invoice);

            $comment = $this->_session->getCommentText(true);
            if ($comment) {
                $invoice->setCommentText($comment);
            }

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            /*$resultPage->setActiveMenu('Magento_Sales::sales_order');*/
            $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Invoice'));
            return $resultPage;
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $this->_redirectToOrder($orderId);
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, 'Cannot create an invoice.');
            return $this->_redirectToOrder($orderId);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::sales_invoice');
    }

    /**
     * Redirect to order view page
     *
     * @param int $orderId
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function _redirectToOrder($orderId)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        return $resultRedirect;
    }
}
