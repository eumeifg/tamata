<?php

namespace CAT\Custom\Model\Entity;

use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use CAT\Custom\Helper\Automation;
use CAT\Custom\Model\Source\Option;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Filesystem\DirectoryList;
use CAT\Custom\Model\ResourceModel\Automation\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magedelight\Sales\Api\OrderRepositoryInterface;
use MDC\Sales\Controller\Adminhtml\Order\InvoiceAndShip\Index;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Event\ManagerInterface as EventManager;

class InvoiceShipment
{
    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var Automation
     */
    protected $automationHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * @var Index
     */
    protected $invoiceAndShipController;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var array
     */
    protected $reportData = [['order_id', 'comments']];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @param Csv $csv
     * @param DateTime $date
     * @param Filesystem $filesystem
     * @param Automation $automationHelper
     * @param CollectionFactory $collectionFactory
     * @param ResourceConnection $resourceConnection
     * @param OrderRepositoryInterface $vendorOrderRepository
     */
    public function __construct(
        Csv $csv,
        DateTime $date,
        Filesystem $filesystem,
        Automation $automationHelper,
        CollectionFactory $collectionFactory,
        ResourceConnection $resourceConnection,
        OrderRepositoryInterface $vendorOrderRepository,
        Index $invoiceAndShipController,
        EventManager $eventManager
    ) {
        $this->csv = $csv;
        $this->_date = $date;
        $this->_filesystem = $filesystem;
        $this->automationHelper = $automationHelper;
        $this->collectionFactory = $collectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->vendorOrderRepository = $vendorOrderRepository;
        $this->invoiceAndShipController = $invoiceAndShipController;
        $this->eventManager = $eventManager;
        $this->connection = $this->resourceConnection->getConnection();
    }

    public function createInvoiceAndShipment($logger) {
        $collection = $this->automationHelper->checkIfSheetAvailable(Option::INVOICE_SHIPMENT_KEYWORD);
        if ($collection) {
            $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('/cat/'.Option::INVOICE_SHIPMENT_KEYWORD.'/').$collection->getFileName();
            $csvData = $this->csv->getData($filePath);

            foreach ($csvData as $row => $data) {
                if ($row >= 1) {
                    if (!empty($data[0])) {
                        try {
                            $sql = $this->connection->select()
                                ->from('md_vendor_order', 'vendor_order_id')
                                ->where('barcode_number=?',$data[0]);
                            $result = $this->connection->fetchOne($sql);
                            if ($result) {
                                $vendorOrderId = $result;
                            } else {
                                $vendorOrderId = count(explode('-', $data[0])) > 1 ? explode('-', $data[0])[1] : '';
                            }
                            if($vendorOrderId) {
                                $vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
                                /** For Admin Confirmed Orders Auto Vendor Confirmed */
                                if($vendorOrder->getStatus() === 'confirmed') {
                                    if ($vendorOrder->getId() && $vendorOrder->getData("is_confirmed") != 1) {
                                        try {
                                            $vendorOrder->setData("is_confirmed", 1)
                                                ->setData('confirmed_at', $this->_date->gmtDate())
                                                ->save();
                                            $this->eventManager->dispatch('vendor_orders_confirm_after', ['vendor_order' => $vendorOrder]);
                                        } catch (\Exception $e) {
                                            $logger->critical('Error > '.$e->getMessage());
                                            $this->addToReport($data[0], __('The order could not confirm.'));
                                        }
                                    }
                                }
                                /** For Already Vendor Confirmed */
                                if (in_array($vendorOrder->getStatus(), ['processing', 'confirmed'])) {
                                    $invoiceGenerated = $this->invoiceAndShipController->generateInvoice($vendorOrderId);
                                    if ($invoiceGenerated) {
                                        $logger->info('Invoice created for Vendor Order '. $data[0]);
                                        $shipmentGenerated = $this->invoiceAndShipController->generateShipment($vendorOrderId);
                                        if ($shipmentGenerated) {
                                            $logger->info('Shipment created for Vendor Order '. $data[0]);
                                        } else {
                                            $logger->info('Shipment not created for Vendor Order '. $data[0]);
                                            $this->addToReport($data[0], 'Shipment not created for Vendor Order.');
                                        }
                                    } else {
                                        $logger->info('Invoice not created for Vendor Order '. $data[0]);
                                        $this->addToReport($data[0], 'Invoice not created for Vendor Order.');
                                    }
                                } else {
                                    $logger->info('Please confirm the order '. $data[0]);
                                    $this->addToReport($data[0], 'Please confirm the order.');
                                }
                            } else {
                                $logger->info('Error: value cannot be empty for vendor order '. $data[0]);
                                $this->addToReport($data[0], 'value cannot be empty for vendor order.');
                            }
                        } catch (NoSuchEntityException $e) {
                            $logger->info( __('Error: %1 ., %2',[$e->getMessage(), $data[0]]));
                            $this->addToReport($data[0], $e->getMessage());
                        }
                    }
                }
            }

            $reportUrl = '';
            if(!empty($this->reportData)) {
                $fileName = Option::INVOICE_SHIPMENT_KEYWORD.'_'.$collection->getImportId().'_'.time().'.csv';
                $reportUrl = $this->automationHelper->generateCsvFile($this->reportData, $fileName, Option::INVOICE_SHIPMENT_KEYWORD);
            }
            $processedTime = $this->_date->gmtDate('Y-m-d H:i:s');
            $this->automationHelper->updateStatus($collection, $reportUrl, $processedTime);
        }
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
}