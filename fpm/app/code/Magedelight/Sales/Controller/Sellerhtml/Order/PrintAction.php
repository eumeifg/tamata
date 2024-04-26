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
namespace Magedelight\Sales\Controller\Sellerhtml\Order;

use Magedelight\Sales\Model\Sales\Order\Pdf\Invoice;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Description of PrintAction
 *
 * @author Rocket Bazaar Core Team
 */
class PrintAction extends \Magedelight\Backend\App\Action
{
    protected $_invoice;

    /**
     * PrintAction constructor.
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoice
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param Invoice $pdfInvoice
     * @param DateTime $dateTime
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoice,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        Invoice $pdfInvoice,
        DateTime $dateTime
    ) {
        $this->_invoice = $invoice;
        $this->vendorOrder = $vendorOrderFactory->create();
        $this->_fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfInvoice = $pdfInvoice;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Pdf_Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['order_id'])) {
            $this->_multiPrint($params['order_id']);
        } else {
            $this->_multiPrint($params['id']);
        }
        return $this->resultRedirectFactory->create()->setPath('*/order/index/', [
                'tab' => '3,0',
                '_current' => true
        ]);
    }

    /**
     * @param $orderIds
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Pdf_Exception
     */
    protected function _multiPrint($orderIds)
    {
        $collection = $this->_invoice->create();
        $collection->addFieldToFilter('vendor_order_id', ['in' => $orderIds]);
        $collection->addFieldToFilter('vendor_id', ['eq' => $this->_auth->getUser()->getVendorId()]);
        if ($collection->count() > 0) {
            $this->_fileFactory->create(
                sprintf('invoice%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
                $this->pdfInvoice->getPdf($collection)->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        } else {
            $this->messageManager->addError(__("Can't print invoice. please try again."));
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
    }
}
