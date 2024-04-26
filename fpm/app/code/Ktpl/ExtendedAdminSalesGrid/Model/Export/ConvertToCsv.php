<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\ExtendedAdminSalesGrid\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;

/**
 * Class ConvertToCsv
 */
class ConvertToCsv extends \Magento\Ui\Model\Export\ConvertToCsv
{
    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var MetadataProvider
     */
    public $metadataProvider;

    /**
     * @var int|null
     */
    protected $pageSize = null;

    /**
     * @var Filter
     */
    protected $filter;

    protected $vendorOrderCollectionFactory;

    private $_vendorHelper;

    protected $_resourceConnection;

    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param int $pageSize
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory,
        \Magedelight\Vendor\Helper\Data $_vendorHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        $pageSize = 200
    ) {
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
        $this->_vendorHelper = $_vendorHelper;
        $this->_resourceConnection = $resourceConnection;
        parent::__construct(
            $filesystem,
            $filter,
            $metadataProvider,
            $pageSize
        );
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();

        if ($component->getName() != "sales_order_grid") {
            return parent::getCsvFile();
        }

        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = $this->metadataProvider->getFields($component);
        $options = $this->metadataProvider->getOptions();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        $i = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize($this->pageSize);
        $totalCount = (int) $dataProvider->getSearchResult()->getTotalCount();
        while ($totalCount > 0) {
            $items = $dataProvider->getSearchResult()->getItems();
            foreach ($items as $item) {

                    $vendorOrders = $this->vendorOrderCollectionFactory->create()
                                ->addFieldToSelect(['order_id','increment_id','status','vendor_id','vendor_order_id'])
                                ->addFieldToFilter("order_id", $item['entity_id']);
                    $allData = $vendorOrders->getData();

                    $allVendroOrderIds = [];
                    $allVendorOrderStatus = [];
                    $allVendorName = [];
                    $shipmentIncrementId = [];

                    foreach ($allData as $data) {

                        /*....To set all vendor order id's in single export column....*/
                        $allVendroOrderIds[] = $data['increment_id'];

                        /*....To set all vendor order status in single export column....*/
                        if ($data['status'] == "pending") {
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
                        }
                        $allVendorOrderStatus[] = $data['status'];

                        /*....To set all vendor name in single export column....*/
                        $vendorName = $this->_vendorHelper->getVendorNameById($data['vendor_id']);
                        $allVendorName[] = $vendorName;

                        $vendorOrderId = $data['vendor_order_id'];

                        $tablename = $this->getTableName1();
                        $connection = $this->_resourceConnection->getConnection();
                        $query1 = "select * FROM ".$tablename." WHERE vendor_order_id =".$vendorOrderId;
                        $salesShipmentEntityId = $connection->fetchAll($query1);
                        foreach ($salesShipmentEntityId as $id) {
                            $entityId = $id['entity_id'];
                            
                            $tablename = $this->getTableName2();
                            $connection = $this->_resourceConnection->getConnection();
                            $query2 = "select increment_id FROM ".$tablename." WHERE entity_id =".$entityId;
                            $shipmentIncrementIdee = $connection->fetchCol($query2);
                            $shipmentIncrementId[] = $shipmentIncrementIdee[0];

                        }


                        /*if($salesShipmentEntityId){
                            $shipmentIncrementId = $shipmentIncrementId[0];
                        } else {
                            $shipmentIncrementId = "";
                        }*/



                        /*....To set all values in respective columns in export CSV....*/
                        $item->setMdVendorOrderIncrementId(implode(",", $allVendroOrderIds));
                        $item->setMdVendorOrderStatus(implode(",", $allVendorOrderStatus));
                        $item->setMdVendorName(implode(",", $allVendorName));
                        $item->setMdVendorOrderShipmentId(implode(",", $shipmentIncrementId));

                    }

                $this->metadataProvider->convertDate($item, $component->getName());
                $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
            }
            $searchCriteria->setCurrentPage(++$i);
            $totalCount = $totalCount - $this->pageSize;
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
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
