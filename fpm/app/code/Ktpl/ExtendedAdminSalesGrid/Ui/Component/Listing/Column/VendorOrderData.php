<?php

namespace Ktpl\ExtendedAdminSalesGrid\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magedelight\Sales\Model\Order as VendorOrder;
use MDC\Sales\Model\Source\Order\PickupStatus;

class VendorOrderData extends Column
{
    const XML_PATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';

    protected $_orderRepository;
    protected $_searchCriteria;
    protected $vendorOrderCollectionFactory;
    private $_vendorHelper;
    protected $_resourceConnection;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magedelight\Vendor\Helper\Data $_vendorHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        array $components = [],
        array $data = []
    )
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
        $this->_config = $config;
        $this->_vendorHelper = $_vendorHelper;
        $this->_resourceConnection = $resourceConnection;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $key =>$item) {
                $vendorOrders = $this->vendorOrderCollectionFactory->create()
                                ->addFieldToSelect(['order_id','increment_id','status','vendor_id','vendor_order_id','pickup_status','is_confirmed', 'vendor_order_with_classification', 'barcode_number'])
                                ->addFieldToFilter("order_id", $item['entity_id']);
                $allData = $vendorOrders->getData();


                $baseAdminUrl = $this->_config->getValue(self::XML_PATH_UNSECURE_BASE_URL, 'default');

                $vendorData = [];
                $statusData = [];
                $vendorNameData = [];
                $salesShipmentEntityId = [];
                $barcode = [];
                foreach ($vendorOrders as $vendorOrder) {
                    /* if ($data['status'] == "pending") {
                        $data['status'] = "New";
                    } else if ($data['status'] == "confirmed") {
                        $data['status'] = "Confirmed";
                    } else if ($data['status'] == "processing") {
                        $data['status'] = "Processing";
                    } else if ($data['status'] == "packed") {
                        $data['status'] = "Packed";
                    } else if ($data['status'] == "shipped") {
                        $data['status'] = "Handover";
                    } else if($data['status'] == "in_transit") {
                        $data['status'] = "In Transit";
                    } */
                    if($vendorOrder->getPickupStatus() == PickupStatus::READY_TO_PICK && $vendorOrder->getStatus() === "processing" && $vendorOrder->getIsConfirmed() === "1" ){
                        $status = $vendorOrder->getConfig()->getStatusLabel(VendorOrder::STATUS_SHIPPED);
                    }
                    elseif ($vendorOrder->getStatus() === "processing" && $vendorOrder->getIsConfirmed() === "1") {
                        $status = "Vendor confirmed";
                    }
                    else{
                        $status = $vendorOrder->getStatusLabel();
                    }
                    $vendorName = $this->_vendorHelper->getVendorNameById($vendorOrder->getVendorId());

                    $vendorOrderId = $vendorOrder->getVendorOrderId();

                    $tablename = $this->getTableName1();
                    $connection = $this->_resourceConnection->getConnection();
                    $query1 = "select increment_id FROM ".$tablename." WHERE vendor_order_id =".$vendorOrderId;
                    $shipmentIncrementId = $connection->fetchOne($query1);
                    if($shipmentIncrementId) {
                            $shipmentIncrementId = " | ".$shipmentIncrementId;
                    }
                    $vendorSubOrderId = !empty($vendorOrder->getVendorOrderWithClassification()) ? $vendorOrder->getVendorOrderWithClassification() : $vendorOrder->getIncrementId();
                    $vendorData[] = "<a href='".$baseAdminUrl."admin/rbsales/order/view/order_id/".$vendorOrder->getOrderId()."/vendor_id/".$vendorOrder->getVendorId()."'>".$vendorSubOrderId."</a> | ".$status." | ".$vendorName.$shipmentIncrementId;
                    //$vendorData[] = "<a href='".$baseAdminUrl."admin/rbsales/order/view/order_id/".$data['order_id']."/vendor_id/".$data['vendor_id']."'>".$data['increment_id']."</a>";
                    $statusData[] = $status;
                    $vendorNameData[] = $vendorName;
                    $vendorOrderShipmentData[] = $shipmentIncrementId;
                    if(!empty($vendorOrder->getBarcodeNumber())) {
                        $barcode[] = $vendorOrder->getBarcodeNumber();
                    }
                }
                $barcode = array_unique($barcode);
                
                $dataSource['data']['items'][$key]['md_vendor_order_increment_id'] = implode("<br/><br/>", $vendorData);
                $dataSource['data']['items'][$key]['md_vendor_order_status'] = $statusData; 
                $dataSource['data']['items'][$key]['md_vendor_name'] = $vendorNameData;
                $dataSource['data']['items'][$key]['order_barcode'] = count($barcode) > 0 ? implode(',', $barcode) : '-';
                //$dataSource['data']['items'][$key]['md_vendor_order_shipment_data'] = $vendorOrderShipmentData;
            }
            //die;
        }
        return $dataSource;
    }

    public function getTableName1()
    {
        return $this->_resourceConnection->getTableName('sales_shipment');
    }

    public function getTableName2()
    {
        return $this->_resourceConnection->getTableName('sales_shipment_grid');
    }
}
