<?php
/*
 * Copyright © 2018 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Magedelight\Sales\Model;

use Magedelight\Sales\Api\OrderRepositoryInterface as VendorOrderRepository;
use Magedelight\Sales\Api\ShippingManagementInterface;
use Magedelight\Sales\Model\Sales\Order\Pdf\Shipment as VendorShipment;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Psr\Log\LoggerInterface as PsrLogger;

class ShippingManagement implements ShippingManagementInterface
{
    CONST PACKING_SLIPS = 'packing_slips/';

    CONST MANIFESTS = 'manifests/';

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magedelight\Sales\Api\Data\FileDownloadInterfaceFactory
     */
    protected $fileDownloadFactory;

    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @var \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory
     */
    protected $customMessageInterface;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactotory;

    /**
     * @var VendorShipment
     */
    protected $pdfShipment;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var VendorOrderRepository
     */
    protected $vendorOrderRepository;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     *
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magedelight\Sales\Api\Data\FileDownloadInterfaceFactory $fileDownloadFactory
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     * @param VendorShipment $vendorShipment
     * @param \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory $customMessageInterface
     * @param \Magento\Framework\Filesystem $filesystem
     * @param PsrLogger $logger
     * @param VendorOrderRepository $vendorOrderRepository
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magedelight\Sales\Api\Data\FileDownloadInterfaceFactory $fileDownloadFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        VendorShipment $vendorShipment,
        \Magedelight\Sales\Api\Data\CustomMessageInterfaceFactory $customMessageInterface,
        \Magento\Framework\Filesystem $filesystem,
        PsrLogger $logger,
        VendorOrderRepository $vendorOrderRepository,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
    ) {
        $this->userContext = $userContext;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->fileDownloadFactory = $fileDownloadFactory;
        $this->logger = $logger;
        $this->customMessageInterface = $customMessageInterface;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->shipmentCollectionFactotory = $shipmentCollectionFactory;
        $this->pdfShipment = $vendorShipment;
        $this->_filesystem = $filesystem;
        $this->logger = $logger;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->vendorRepository = $vendorRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePackingSlip($vendorOrderId)
    {
        $fileDownload = $this->fileDownloadFactory->create();
        if ($vendorOrderId) {
            $vendorId = $this->userContext->getUserId();
            $collection = $this->getShipmentCollection($vendorId, $vendorOrderId);
            if (!$collection->getSize()) {
                throw new NoSuchEntityException(__('There are no printable documents related to selected Order.'));
            }
            try{
                $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
            }catch (\Exception $exception){
                $fileDownload->setStatus(__('Specified order does not exists.'));
            }
            $dir = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $file = null;
            $fileName =  self::PACKING_SLIPS.sprintf('packingslip_%s.pdf', $vendorOrder->getIncrementId());
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $content = $this->pdfShipment->getPdf($collection->getItems())->render();
            if (is_array($content)) {
                if (!isset($content['type']) || !isset($content['value'])) {
                    throw new \InvalidArgumentException("Invalid arguments. Keys 'type' and 'value' are required.");
                }
                if ($content['type'] == 'filename') {
                    $file = $content['value'];
                    if (!$dir->isFile($file)) {
                        throw new \Exception((string)new \Magento\Framework\Phrase('File not found'));
                    }
                    $contentLength = $dir->stat($file)['size'];
                }
            }

            if ($content !== null) {
                $dir->writeFile($fileName, $content);
                $fileDownload->setStatus(__('Packing Slip Generation Completed.'));
                $fileDownload->setFilePath($mediaUrl . $fileName);
            } else {
                $fileDownload->setStatus(__('Packing Slip Generation Failed.'));
            }
        }

        return $fileDownload;
    }

    protected function getShipmentCollection($vendorId, $vendorOrderId)
    {
        $shipmentsCollection = $this->shipmentCollectionFactory
            ->create()
            ->addFieldToFilter('vendor_order_id' , $vendorOrderId)
            ->addFieldToFilter('vendor_id', ['eq' => $vendorId ]);

        return $shipmentsCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function generateManifest($vendorOrderId)
    {
        $fileDownload = $this->fileDownloadFactory->create();
        if ($vendorOrderId) {
            $vendorId = $this->userContext->getUserId();

            try{
                $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
            }catch (\Exception $exception){
                $fileDownload->setStatus(__('Specified order does not exists.'));
            }

            if (empty($vendorOrder)) {
                throw new NoSuchEntityException(__('Specified order does not exists.'));
            }

            $vendorData = $this->vendorRepository->getById($vendorId);
            $shipmentsCollection = $this->getShipmentCollection($vendorId, $vendorOrderId);
            $detailsAddress = $vendorData->getAddress1() . ", " . $vendorData->getAddress2() . "\n"
                . $vendorData->getCity() . ", " . $vendorData->getPincode() . ", "
                . $vendorData->getRegion() . ", " . $vendorData->getCountry();
            $pdf = new \Zend_Pdf();
            $pageNumber = 0;
            foreach ($shipmentsCollection as $shipment) {
                $trackingNumber = $note = '';
                $note = $shipment->getCustomerNote();
                $tracks = $shipment->getAllTracks();
                foreach ($tracks as $track) {
                    $trackingNumber = $track->getNumber();
                }
                $this->createManifestPages(
                    $pdf,
                    $pageNumber,
                    $trackingNumber,
                    $vendorData->getBusinessName(),
                    $detailsAddress,
                    $vendorOrder->getIncrementId(),
                    $note
                );
                $pageNumber++;
            }

            $dir = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $file = null;
            $fileName = self::MANIFESTS.'manifest_' . $vendorOrder->getIncrementId() . '.pdf';
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $content = $pdf->render();
            if (is_array($content)) {
                if (!isset($content['type']) || !isset($content['value'])) {
                    throw new \InvalidArgumentException("Invalid arguments. Keys 'type' and 'value' are required.");
                }
                if ($content['type'] == 'filename') {
                    $file = $content['value'];
                    if (!$dir->isFile($file)) {
                        throw new \Exception((string)new \Magento\Framework\Phrase('File not found'));
                    }
                    $contentLength = $dir->stat($file)['size'];
                }
            }

            if ($content !== null) {
                $dir->writeFile($fileName, $content);
                $fileDownload->setStatus(__('Manifest Generation Completed.'));
                $fileDownload->setFilePath($mediaUrl . $fileName);
            } else {
                $fileDownload->setStatus(__('Manifest Generation Failed.'));
            }
        }

        return $fileDownload;
    }

    protected function createManifestPages(
        $pdf,
        $pageNumber = 0,
        $trackingNumber = '',
        $vendorBusinessName = '',
        $detailsAddress = '',
        $incrementOrderId,
        $note = ''
    ) {
        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[$pageNumber]; /* this will get reference to the first page.*/
        $trackingNumber = ($trackingNumber) ?: 'N/A';
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 15);
        $page->setStyle($style);
        $width = $page->getWidth();
        $hight = $page->getHeight();
        $x = 30;
        $pageTopalign = 850; /*default PDF page height*/
        $this->y = 850 - 100; /*print table row from page top – 100px*/
        /*Draw table header row’s*/
        $style->setFont($font, 16);
        $bottomLine = 600;
        $page->setStyle($style);

        $page->drawRectangle(30, $this->y + 10, $page->getWidth()-30, $this->y +70, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $style->setFont($font, 15);
        $page->setStyle($style);
        $page->drawText(__("Supplier Details"), $x + 5, $this->y+50, 'UTF-8');
        $style->setFont($font, 11);
        $page->setStyle($style);
        $page->drawText(__("Name: %1", $vendorBusinessName), $x + 5, $this->y+33, 'UTF-8');
        $page->drawText(__("Address: %1", $detailsAddress), $x + 5, $this->y+16, 'UTF-8');

        $style->setFont($font, 12);
        $page->setStyle($style);
        $page->drawText(__("Sr No"), $x + 10, $this->y-10, 'UTF-8');
        $page->drawText(__("Tracking Number"), $x + 60, $this->y-10, 'UTF-8');
        $page->drawText(__("Order Id"), $x + 200, $this->y-10, 'UTF-8');
        $page->drawText(__("RTS Done on"), $x + 310, $this->y-10, 'UTF-8');
        /* Add note only if note data is available */
        if (trim($note) != '') {
            $page->drawText(__("Notes"), $x + 10, $this->y-200, 'UTF-8');
        }

        $style->setFont($font, 10);
        $page->setStyle($style);
        $page->drawText("1", $x + 10, $this->y-30, 'UTF-8');
        $page->drawText(__($trackingNumber), $x + 60, $this->y-30, 'UTF-8');
        $page->drawText($incrementOrderId, $x + 200, $this->y-30, 'UTF-8');
        $page->drawText("", $x + 330, $this->y-30, 'UTF-8');
        /* Add note data with line break */
        if (trim($note) != '') {
            $note = wordwrap($note, 115);/*preg_replace("/(.{100})/", "$1<br />", $note);*/
            $i = 215;
            foreach (explode("\n", $note) as $i => $line) {
                $bottomLine = $this->y - 215 - ($i * 12);
                $page->drawText($line, 65, $bottomLine, 'UTF-8');
            }
        }
        $pro = "";
        $page->drawText($pro, $x + 60, $this->y-30, 'UTF-8');

        $page->drawRectangle(30, $this->y -62, $page->getWidth()-30, $this->y + 10, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

        $page->drawText(__("Pickup in time:"), $x + 10, $this->y-80, 'UTF-8');
        $page->drawText(__("_____________________________"), $x + 80, $this->y-80, 'UTF-8');

        $page->drawText(__("Total Items picked:"), $x + 280, $this->y-80, 'UTF-8');
        $page->drawText(__("_____________________________"), $x + 360, $this->y-80, 'UTF-8');

        $page->drawText(__("Pickup out time:"), $x + 10, $this->y-100, 'UTF-8');
        $page->drawText(__("_____________________________"), $x + 80, $this->y-100, 'UTF-8');

        $page->drawText(__("FE Name:"), $x + 10, $this->y-120, 'UTF-8');
        $page->drawText(__("_____________________________"), $x + 80, $this->y-120, 'UTF-8');

        $page->drawText(__("Seller Name:"), $x + 280, $this->y-120, 'UTF-8');
        $page->drawText(__($vendorBusinessName), $x + 360, $this->y-120, 'UTF-8');

        $page->drawText(__("FE Signature:"), $x + 10, $this->y-140, 'UTF-8');
        $page->drawText(__("_____________________________"), $x + 80, $this->y-140, 'UTF-8');

        $page->drawText(__("Seller Signature:"), $x + 280, $this->y-140, 'UTF-8');
        $page->drawText(__("_____________________________"), $x + 360, $this->y-140, 'UTF-8');

        $style->setFont($font, 9);
        $page->setStyle($style);
        $page->drawText(__("This is a system generated document"), $x + 180, $bottomLine - 60, 'UTF-8');
        $page->drawRectangle(
            30,
            $this->y -62,
            $page->getWidth()-30,
            $bottomLine - 70,
            \Zend_Pdf_Page::SHAPE_DRAW_STROKE
        );
        $style->setFont($font, 15);
        $page->setStyle($style);
    }
}
