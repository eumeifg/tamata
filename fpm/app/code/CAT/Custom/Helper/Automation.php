<?php

namespace CAT\Custom\Helper;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use CAT\Custom\Model\Source\Option;
use CAT\Custom\Model\ResourceModel\Automation\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Automation extends AbstractHelper
{
    const BULK_STORE_CREDIT_ENABLE = 'automation/store_credit_config/enable';
    const STORE_CREDIT_DELETE_DAYS = 'automation/store_credit_config/delete_in_days';

    const BULK_VENDOR_PAYMENT_STATUS_ENABLE = 'automation/vendor_payment_config/enable';
    const VENDOR_PAYMENT_DELETE_DAYS = 'automation/vendor_payment_config/delete_in_days';

    const BULK_INVOICE_SHIPMENT_ENABLE = 'automation/invoice_shipment_config/enable';
    const INVOICE_SHIPMENT_DELETE_DAYS = 'automation/invoice_shipment_config/delete_in_days';

    const PRODUCT_OFFER_UPDATE_ENABLE = 'automation/offer_update_config/enable';

    const PRODUCT_SKU_UPDATE_ENABLE = 'automation/sku_update_config/enable';
    const PRODUCT_SKU_BATCH_LIMIT = 'automation/sku_update_config/batch_limit';

    const VENDOR_QTY_UPDATE_ENABLE = 'automation/vendor_qty_update_config/enable';
    const VENDOR_QTY_BATCH_LIMIT = 'automation/vendor_qty_update_config/batch_limit';

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var [type]
     */
    protected $scopeConfig;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @param File $file
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        File $file,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeConfig,
        DateTime $date
    ) {
        $this->file = $file;
        $this->_date = $date;
        $this->_filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->mediaDirectory = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    /**
     * @param $entityType
     */
    public function checkIfSheetAvailable($entityType) {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('*');
        $collection->addFieldToFilter('entity_type',['eq' => $entityType]);
        $collection->addFieldToFilter('status', ['eq' => 0]);
        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        }
        return false;
    }

    /**
     * @param $obj
     * @param $reportUrl
     * @param $processedTime
     * @return void
     */
    public function updateStatus($obj, $reportUrl, $processedTime)
    {
        if($reportUrl) {
            $obj->setReportUrl($reportUrl);
        }
        $obj->setStatus(1)->setProcessedAt($processedTime)->save();
    }

    /**
     * @param $array
     * @param $fileName
     * @param $entityType
     * @return void
     */
    public function generateCsvFile($array, $fileName, $entityType)
    {
        $filePath = 'cat/'.$entityType.'/report/';
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

        $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $url . $filePath . $fileName;
    }

    /**
     * @param $entity
     */
    public function getLogger($entity) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/'.$entity.'.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;
    }

    /**
     * @param string $entity
     */
    public function getEntityAutomationEnable($entity) {
        if($entity === Option::STORE_CREDIT_KEYWORD) {
            return $this->scopeConfig->getValue(self::BULK_STORE_CREDIT_ENABLE, ScopeInterface::SCOPE_STORE);
        } /*elseif($entity === Option::VENDOR_PAYMENT_STATUS) {
            return $this->scopeConfig->getValue(self::BULK_VENDOR_PAYMENT_STATUS_ENABLE, ScopeInterface::SCOPE_STORE);
        } elseif($entity === Option::INVOICE_SHIPMENT_KEYWORD){
            return $this->scopeConfig->getValue(self::BULK_INVOICE_SHIPMENT_ENABLE, ScopeInterface::SCOPE_STORE);
        }*/
        elseif($entity === Option::PRODUCT_OFFERS_KEYWORD){
            return $this->scopeConfig->getValue(self::PRODUCT_OFFER_UPDATE_ENABLE, ScopeInterface::SCOPE_STORE);
        }
        elseif($entity === Option::PRODUCT_SKU_KEYWORD){
            return $this->scopeConfig->getValue(self::PRODUCT_SKU_UPDATE_ENABLE, ScopeInterface::SCOPE_STORE);
        }
        elseif($entity === Option::VENDOR_QTY_KEYWORD){
            return $this->scopeConfig->getValue(self::VENDOR_QTY_UPDATE_ENABLE, ScopeInterface::SCOPE_STORE);
        }
        else {
            return false;
        }
    }

    public function removeOldBulkRecords($entity) {
        if($entity === Option::STORE_CREDIT_KEYWORD) {
            $value = $this->scopeConfig->getValue(self::STORE_CREDIT_DELETE_DAYS);
        } /*elseif ($entity === Option::INVOICE_SHIPMENT_KEYWORD) {
            $value = $this->scopeConfig->getValue(self::INVOICE_SHIPMENT_DELETE_DAYS);
        } elseif ($entity === Option::VENDOR_PAYMENT_STATUS) {
            $value = $this->scopeConfig->getValue(self::VENDOR_PAYMENT_DELETE_DAYS);
        }*/

        if(!empty($value) && $value > 0) {
            $currentTime = $this->_date->gmtDate('Y-m-d H:i:s');
            $finalDate = date('Y-m-d H:i:s', strtotime($currentTime . "-".$value." day"));
            $collection = $this->collectionFactory->create();
            $collection->addFieldToSelect(['bulk_import_id','file_name', 'report_url']);
            $collection->addFieldToFilter('status', ['eq' => 1]);
            $collection->addFieldToFilter('created_at', ['lteq' => $finalDate]);
            $collection->addFieldToFilter('entity_type', ['eq' => $entity]);

            // echo "<pre>"; print_r($collection->getData()); echo "</pre>"; die;

            if($collection->getSize() > 0) {
                foreach ($collection as $item) {
                    $filePath = 'cat/'.$entity.'/report/';
                    if ($item->getReportUrl()) {
                        $reportFileName = explode('/', $item->getReportUrl());
                        $reportTarget = $this->mediaDirectory->getAbsolutePath($filePath).end($reportFileName);
                        $this->removeFile($reportTarget);
                    }
                    $fileTarget = $this->mediaDirectory->getAbsolutePath('cat/').$item->getFileName();
                    $this->removeFile($fileTarget);
                    $item->delete();
                }
            }
        }
    }

    public function removeFile($filepath)
    {
        if ($this->file->isExists($filepath)) {
            $this->file->deleteFile($filepath);
        }
    }

    public function getSkuBatchLimit() {
        return $this->scopeConfig->getValue(self::PRODUCT_SKU_BATCH_LIMIT);
    }

    public function getvendorqtyBatchLimit() {
        return $this->scopeConfig->getValue(self::VENDOR_QTY_BATCH_LIMIT);
    }
}
