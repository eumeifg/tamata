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
namespace Magedelight\Sales\Controller\Adminhtml\Order;

class View extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * View constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrder
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
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Sales\Model\OrderFactory $vendorOrder,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $logger
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
        $this->_vendorOrder = $vendorOrder;
    }

    /**
     * @return false|\Magento\Sales\Api\Data\OrderInterface
     */
    protected function _initOrder()
    {
        $order = parent::_initOrder();
        if ($order) {
            $vendorOrder =  $this->_vendorOrder->create()->getByOriginOrderId(
                $order->getId(),
                $this->getRequest()->getParam('vendor_id'),
                $this->getRequest()->getParam('vendor_order_id')
            );
            $this->_coreRegistry->register('vendor_order', $vendorOrder);
        }
        return $order;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Sales::sales_order');
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('Orders'), __('Orders'));
        return $resultPage;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $vendorOrder = $this->_coreRegistry->registry('vendor_order');
        if (!$vendorOrder) {
            $vendorOrder = $this->_vendorOrder->create()->getByOriginOrderId(
                $this->getRequest()->getParam('order_id'),
                $this->getRequest()->getParam('vendor_id'),
                $this->getRequest()->getParam('vendor_order_id')
            );
        }

        if ($order = $this->_initOrder()) {
            $this->_initAction();
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(sprintf("#%s", $vendorOrder->getIncrementId()));
            $this->_view->renderLayout();
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::view_detail_order');
    }
}
