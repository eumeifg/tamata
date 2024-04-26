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

use Magedelight\Backend\App\Action\Context;
use Magedelight\Sales\Model\Sales\Order\Pdf\Shipment as VendorShipment;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;

/**
 * @author Rocket Bazaar Core Team
 *  Created at 10 Jun, 2016 6:08:31 PM
 */
class Pdfshipments extends \Magedelight\Backend\App\Action
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
     * @var VendorShipment
     */
    protected $pdfShipment;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactotory;

    /**
     *
     * @param Context $context
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param VendorShipment $vendorShipment
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        FileFactory $fileFactory,
        VendorShipment $vendorShipment,
        ShipmentCollectionFactory $shipmentCollectionFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfShipment = $vendorShipment;
        $this->shipmentCollectionFactotory = $shipmentCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Print shipments for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Redirect
     * @throws \Zend_Pdf_Exception
     */
    public function execute()
    {
        $orderIds = [];

        $orderIds[] = $this->getRequest()->getParam('order_id');

        $shipmentsCollection = $this->shipmentCollectionFactotory->create()
            ->addFieldToFilter('vendor_order_id', ['eq' => $this->getRequest()->getParam('vendor_order_id') ]);

        if (!$shipmentsCollection->getSize()) {
            $this->messageManager->addErrorMessage(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
        }
        return $this->fileFactory->create(
            sprintf('packingslip%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $this->pdfShipment->getPdf($shipmentsCollection->getItems())->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
    }
}
