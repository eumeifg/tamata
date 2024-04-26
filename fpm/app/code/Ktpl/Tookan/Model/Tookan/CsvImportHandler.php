<?php

namespace Ktpl\Tookan\Model\Tookan;

use Exception;
use Ktpl\Tookan\Model\Config\Source\TookanStatus;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magedelight\Sales\Model\OrderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Sales\Model\Order\Shipment;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Ktpl\Tookan\Model\StatusLogFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

/**
 * Tax Rate CSV Import Handler
 *
 * @api
 * @since 100.0.2
 */
class CsvImportHandler
{
    /**
     * Collection of publicly available stores
     *
     * @var \Magento\Store\Model\ResourceModel\Store\Collection
     */
    protected $_publicStores;

    /**
     * CSV Processor
     *
     * @var Csv
     */
    protected $csvProcessor;

    protected $headers = [];
    /**
     * @var OrderFactory
     */
    private $vendorOrderFactory;

    private $shipmentModel;
    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    protected $_modelStatusLogFactory;

    private $order;

     /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * CsvImportHandler constructor.
     * @param \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection
     * @param Csv $csvProcessor
     * @param OrderFactory $vendorOrderFactory
     * @param Shipment $shipmentModel
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StatusLogFactory $modelStatusLogFactory
     * @param OrderInterface $order
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection,
        Csv $csvProcessor,
        OrderFactory $vendorOrderFactory,
        Shipment $shipmentModel,
        ShipmentRepositoryInterface $shipmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StatusLogFactory $modelStatusLogFactory,
        OrderInterface $order,
        EventManagerInterface $eventManager
    ) {
        // prevent admin store from loading
        $this->_publicStores = $storeCollection->setLoadDefault(false);
        $this->csvProcessor = $csvProcessor;
        $this->vendorOrderFactory = $vendorOrderFactory;
        $this->shipmentModel = $shipmentModel;
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_modelStatusLogFactory = $modelStatusLogFactory;
        $this->order = $order;
        $this->eventManager = $eventManager;
    }

    /**
     * Import Tax Rates from CSV file
     *
     * @param array $file file info retrieved from $_FILES array
     * @return void
     * @throws LocalizedException
     */
    public function importFromCsvFile($file)
    {
        if (!isset($file['tmp_name'])) {
            throw new LocalizedException(__('Invalid file upload attempt.'));
        }
        $ratesRawData = $this->csvProcessor->getData($file['tmp_name']);
        // first row of file represents headers
        $fileFields = $ratesRawData[0];
        $validFields = $this->_filterFileFields($fileFields);
        $invalidFields = [];//array_diff_key($fileFields, $validFields);
        $ratesData = $this->_filterRateData($ratesRawData, $invalidFields, $validFields);

        foreach ($ratesData as $rowIndex => $dataRow) {
            // skip headers and non completed order.
            if ($rowIndex == 0 || ($dataRow['Task_Status'] != "Completed" && $dataRow['Task_Type'] != "Delivery")) {
                continue;
            }

            /*....Start to save completed order date in seperate table(md_vendor_order_status_log)....*/
            $shipmentIncrementId = substr($dataRow['Order_ID'], 1);
            $shipment = $this->shipmentModel->loadByIncrementId($shipmentIncrementId);

            if($shipment && $shipment->getId() && $shipment->getVendorOrderId()){
                // /*....To save item ids to the custom table....*/
                $allItemIds = [];
                foreach ($shipment->getItems() as $item) {
                    array_push($allItemIds, $item->getItemId());
                    $finalItemIds = implode(",", $allItemIds);
                }

                $collection = $this->_modelStatusLogFactory->create();
                $collection->setShipmentId($shipment->getIncrementId());
                $collection->setShippedItemIds($finalItemIds);
                $collection->save();
                /*....End to save completed order date in seperate table(md_vendor_order_status_log)....*/
                $this->updateVendorOrderStatus($shipment);
            }
        }
    }

    /**
     * Filter file fields (i.e. unset invalid fields)
     *
     * @param array $fileFields
     * @return string[] filtered fields
     */
    protected function _filterFileFields(array $fileFields)
    {
        $filteredFields = $this->getRequiredCsvFields();
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $fileFieldsNum = count($fileFields);

        // process title-related fields that are located right after required fields with store code as field name)
        for ($index = $requiredFieldsNum; $index < $fileFieldsNum; $index++) {
            $titleFieldName = $fileFields[$index];
            if ($this->_isStoreCodeValid($titleFieldName)) {
                // if store is still valid, append this field to valid file fields
                $filteredFields[$index] = $titleFieldName;
            }
        }

        return $filteredFields;
    }

    /**
     * Retrieve a list of fields required for CSV file (order is important!)
     *
     * @return array
     */
    public function getRequiredCsvFields()
    {
        // indexes are specified for clarity, they are used during import
        return [
            __('Task_ID'),
            __('Order_ID'),
            __('Task_Type'),
            __('Task_Status')
        ];
    }

    /**
     * Check if public store with specified code still exists
     *
     * @param string $storeCode
     * @return boolean
     */
    protected function _isStoreCodeValid($storeCode)
    {
        $isStoreCodeValid = false;
        foreach ($this->_publicStores as $store) {
            if ($storeCode === $store->getCode()) {
                $isStoreCodeValid = true;
                break;
            }
        }
        return $isStoreCodeValid;
    }

    /**
     * Filter rates data (i.e. unset all invalid fields and check consistency)
     *
     * @param array $rateRawData
     * @param array $invalidFields assoc array of invalid file fields
     * @param array $validFields assoc array of valid file fields
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _filterRateData(array $rateRawData, array $invalidFields, array $validFields)
    {
        foreach ($rateRawData as $rowIndex => $dataRow) {
            // skip empty rows
            if (count($dataRow) <= 1) {
                unset($rateRawData[$rowIndex]);
                continue;
            }
            if ($rowIndex == 0) {
                $this->headers = $this->getHeadersFromCsv($dataRow);
                continue;
            }
            $exportRowData[$rowIndex] = $this->getFormattedCSVData($rateRawData, $dataRow, $validFields, $rowIndex);
        }
        return $exportRowData;
    }

    /**
     * @param $dataRow
     * @return mixed
     */
    protected function getHeadersFromCsv($dataRow)
    {
        return $dataRow;
    }

    /**
     * @param $rateRawData
     * @param $dataRow
     * @param $validFields
     * @param $rowIndex
     * @return array
     */
    private function getFormattedCSVData($rateRawData, $dataRow, $validFields, $rowIndex)
    {
        $exportRowData = [];
        // unset invalid fields from data row
        foreach ($dataRow as $fieldIndex => $fieldValue) {
            foreach ($validFields as $validField) {
                $key = array_search($validField->getText(), $this->headers);
                if ($fieldIndex == $key) {
                    $exportRowData[$validField->getText()] = $rateRawData[$rowIndex][$fieldIndex];
                }
            }
        }
        return $exportRowData;
    }

    /**
     * @param $shipment
     */
    protected function updateVendorOrderStatus($shipment)
    {
        $deliveredStatus = VendorOrder::STATUS_COMPLETE;
        try {
            $vendorOrder = $this->vendorOrderFactory->create()->getCollection()->addFieldToFilter(
                'vendor_order_id',
                ['eq' => $shipment->getVendorOrderId()]
            )->addFieldToFilter(
                'vendor_id',
                ['eq' => $shipment->getVendorId()]
            )->getFirstItem();
            if ($vendorOrder->getId() && $vendorOrder->getStatus() != $deliveredStatus && !$vendorOrder->canShip()
            && $this->checkAllShipmentAreOutOfWarehouse($shipment->getOrderId(), $vendorOrder->getVendorOrderId())) {
                $vendorOrder->setStatus($deliveredStatus)->save();
             $this->eventManager->dispatch('vendor_orders_delivered_after', ['vendor_order' => $vendorOrder]);
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @param $orderId
     * @param $vendorOrderId
     * @return bool
     */
    public function checkAllShipmentAreOutOfWarehouse($orderId, $vendorOrderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId, 'eq')
            ->addFilter('vendor_order_id', $vendorOrderId, 'eq')
            ->addFilter('tookan_status', TookanStatus::OUT_FOR_DELIVERY, 'neq')->create();
        $count = $this->shipmentRepository->getList($searchCriteria)->getTotalCount();
        return ($count > 0) ? false : true;
    }
}
