<?php

namespace MDC\Sales\Model;

use MDC\Sales\Model\ResourceModel\BulkInvoiceShip\CollectionFactory;
use MDC\Sales\Model\BulkInvoiceShipFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use MDC\Sales\Controller\Adminhtml\Order\InvoiceAndShip\Index;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory as VendorOrderCollectionFactory;

class BulkInvoiceShipmentModel
{
    const OLD_BULK_RECORD_REMOVE_DATE = 'sales/cron_config/remove_tat';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;

    /**
     * @var Index
     */
    protected $invoiceAndShipController;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var VendorOrderCollectionFactory
     */
    protected $vendorOrderCollectionFactory;

    /**
     * @var array
     */
    protected $reportData = [['order_id', 'comments']];

    /**
     * BulkInvoiceShipmentModel constructor.
     * @param \Magento\Framework\File\Csv $csv
     * @param Index $invoiceAndShipController
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param File $file
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param EventManager $eventManager
     * @param VendorOrderCollectionFactory $vendorOrderCollectionFactory
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\File\Csv $csv,
        Index $invoiceAndShipController,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        File $file,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        EventManager $eventManager,
        VendorOrderCollectionFactory $vendorOrderCollectionFactory
    ) {
        $this->csv = $csv;
        $this->invoiceAndShipController = $invoiceAndShipController;
        $this->collectionFactory = $collectionFactory;
        $this->_filesystem = $filesystem;
        $this->file = $file;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->eventManager = $eventManager;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
        $this->mediaDirectory = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createBulkInvoiceShipment() {
        $collection = $this->checkIfSheetAvailable();
        if ($collection) {
            $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('/cat/').$collection->getFileName();
            $csvData = $this->csv->getData($filePath);
            foreach ($csvData as $row => $data) {
                if ($row >= 1) {
                    if (!empty($data[0])) {
                        try {
                            $vendorOrderCollection = $this->vendorOrderCollectionFactory->create();
                            $vendorOrderCollection->addFieldToSelect(['status', 'order_id', 'vendor_id', 'increment_id', 'is_confirmed']);
                            $vendorOrderCollection->addFieldToFilter('vendor_order_with_classification', ['eq' => $data[0]]);

                            if ($vendorOrderCollection->getSize()) {
                                $vendorOrder = $vendorOrderCollection->getFirstItem();
                                /** For Admin Confirmed Orders Auto Vendor Confirmed */
                                if($vendorOrder->getStatus() === 'confirmed') {
                                    if ($vendorOrder->getId() && $vendorOrder->getData("is_confirmed") != 1) {
                                        try {
                                            $vendorOrder->setData("is_confirmed", 1)
                                                ->setData('confirmed_at', $this->dateTime->gmtDate())
                                                ->save();
                                            /*$this->eventManager->dispatch('vendor_orders_confirm_after', ['vendor_order' => $vendorOrder]);*/
                                        } catch (\Exception $e) {
                                            $this->addToReport($data[0], __('The order could not confirm.'));
                                        }
                                    }
                                }
                                /** For Already Vendor Confirmed */
                                if (in_array($vendorOrder->getStatus(), ['processing', 'confirmed'])) {
                                    $invoiceGenerated = $this->invoiceAndShipController->generateInvoice($vendorOrder->getId());
                                    if ($invoiceGenerated) {
                                        $shipmentGenerated = $this->invoiceAndShipController->generateShipment($vendorOrder->getId());
                                        if (!$shipmentGenerated) {
                                            $this->addToReport($data[0], 'Shipment not created for Vendor Order.');
                                        }
                                    } else {
                                        $this->addToReport($data[0], 'Invoice not created for Vendor Order.');
                                    }
                                } else {
                                    $this->addToReport($data[0], 'Please confirm the order.');
                                }
                            } else {
                                $this->addToReport($data[0], 'No record found.');
                            }
                        } catch (NoSuchEntityException $e) {
                            $this->addToReport($data[0], $e->getMessage());
                        }
                    }
                }
            }
            $currentTime = $this->dateTime->gmtDate('Y-m-d H:i:s');
            $collection->setStatus(1);
            $collection->setProcessedAt($currentTime);

            if(!empty($this->reportData)) {
                $fileName = 'bulk_'.$collection->getBulkImportId().'_'.time().'.csv';
                $url = $this->generateCsvFile($this->reportData, $fileName);
                $collection->setReportUrl($url);
            }
            $collection->save();
        }
    }

    public function checkIfSheetAvailable() {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect(['bulk_import_id','file_name']);
        $collection->addFieldToFilter('status', ['eq' => 0]);
        $collection->addFieldToFilter('process_status', ['eq' => 0]);
        if ($collection->count() > 0) {
            $col = $collection->getFirstItem();
            $col->setProcessStatus(1)->save();
            return $col;
        }
        return false;
    }

    /**
     * @param string $subOrderId
     * @param string $msg
     * @return void
     */
    public function addToReport($subOrderId, $msg = 'success')
    {
        $this->reportData[] = [
            'order_id' => $subOrderId,
            'comment' => $msg
        ];
    }

    /**
     * @param array $array
     * @param string $fileName
     * @return string
     */
    public function generateCsvFile($array, $fileName)
    {
        $filePath = 'cat/bulk_invoice_ship/report/';
        $target = $this->mediaDirectory->getAbsolutePath($filePath);
        $this->file->createDirectory($target);

        if ($this->file->isExists($target . $fileName)) {
            $this->file->deleteFile($target . $fileName);
        }

        $fileHandler = fopen($target . $fileName, 'w');
        foreach ($array as $item) {
            $this->file->filePutCsv($fileHandler, $item);
        }
        fclose($fileHandler);

        $url = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $url . $filePath . $fileName;
    }
}