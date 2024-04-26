<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Controller\Sellerhtml\Commission;

use Magento\Framework\App\Filesystem\DirectoryList;

class PrintAction extends \Magedelight\Backend\App\Action
{

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Shipment
     */
    protected $pdfCommission;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactotory;

    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magedelight\Commissions\Model\Commission\Pdf\Invoice $invoice
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magedelight\Commissions\Model\Commission\Pdf\Invoice $invoice
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfCommission = $invoice;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Print shipments for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $shipmentsCollection = $this->collectionFactory->create();
        
        $shipmentsCollection->getSelect()->columns([
            'total_fees' => new \Zend_Db_Expr('(total_commission + marketplace_fee + cancellation_fee + service_tax)')
        ]);
        
        $shipmentsCollection->addFieldToFilter('vendor_id', ['eq' => $this->_auth->getUser()->getVendorId()])
            ->addFieldToFilter('status', ['eq' => 1]);
        
        $shipmentsCollection->addFieldToFilter('vendor_payment_id', $this->getRequest()->getParam('id'));
        if (!$shipmentsCollection->getSize()) {
            $this->messageManager->addError(__('There are no printable documents related to selected payment.'));
            return $this->resultRedirectFactory->create()->setPath($this->getComponentRefererUrl());
        }
        return $this->fileFactory->create(
            sprintf('commission-invoice%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $this->pdfCommission->getPdf($shipmentsCollection)->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::financial');
    }
}
