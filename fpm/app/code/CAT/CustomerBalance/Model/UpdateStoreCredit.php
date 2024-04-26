<?php

namespace CAT\CustomerBalance\Model;

use CAT\CustomerBalance\Model\ResourceModel\BulkImport\CollectionFactory;
use CAT\CustomerBalance\Model\BulkImportFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class UpdateStoreCredit
 * @package CAT\CustomerBalance\Model
 */
class UpdateStoreCredit
{
    const OLD_BULK_RECORD_REMOVE_DATE = 'sales/cron_config/remove_tat';
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \CAT\CustomerBalance\Model\BulkImportFactory
     */
    protected $bulkImportFactory;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var BalanceFactory
     */
    protected $_balanceFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var array
     */
    protected $reportData = [['email_id', 'comments']];

    /**
     *
     * @param CollectionFactory $collectionFactory
     * @param BulkImportFactory $bulkImportFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Csv $csv
     * @param StoreManagerInterface $storeManager
     * @param BalanceFactory $balanceFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param File $file
     * @param DateTime $dateTime
     * @param ScopeConfigInterface $scopeInterface
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        BulkImportFactory $bulkImportFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Csv $csv,
        StoreManagerInterface $storeManager,
        BalanceFactory $balanceFactory,
        CustomerRepositoryInterface $customerRepository,
        File $file,
        DateTime $dateTime,
        ScopeConfigInterface $scopeInterface
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->bulkImportFactory = $bulkImportFactory;
        $this->_filesystem = $filesystem;
        $this->csv = $csv;
        $this->_storeManager = $storeManager;
        $this->_balanceFactory = $balanceFactory;
        $this->customerRepository = $customerRepository;
        $this->file = $file;
        $this->dateTime = $dateTime;
        $this->_scopeConfig = $scopeInterface;
        $this->mediaDirectory = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    /**
     * @param $logger
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateStoreCredit($logger) {
        $collection = $this->checkIfSheetAvailable();
        if ($collection) {
            $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('/cat/store-credit/').$collection->getFileName();
            $csvData = $this->csv->getData($filePath);
            $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
            $logger->info(__("Website ID . ".$currentWebsiteId));
            $currentWebsiteId = !empty($currentWebsiteId) ? $currentWebsiteId : 1;

            foreach ($csvData as $row => $data) {
                if ($row >= 1) {
                    try {
                        $customer = $this->customerRepository->get($data[0],$currentWebsiteId);
                        $balance = $this->_balanceFactory->create();
                        $balance->setCustomer($customer)
                            ->setWebsiteId($currentWebsiteId)
                            ->setAmountDelta($data[1])
                            ->setHistoryAction(
                                \Magento\CustomerBalance\Model\Balance\History::ACTION_UPDATED
                            )
                            ->setUpdatedActionAdditionalInfo(__('Added By www.tamata.com'))
                            ->save();
                        $logger->info(__("Successfully added for email %1.", $data[0]));
                    } catch (NoSuchEntityException $e) {
                        $logger->info(__("The customer email isn't defined."));
                        $this->addToReport($data[0], 'The customer email isn\'t defined.');
                    }
                }
            }
            
            if(!empty($this->reportData)) {
                $fileName = 'bulk_sc_'.$collection->getImportId().'_'.time().'.csv';
                $url = $this->generateCsvFile($this->reportData, $fileName);
            }
            $currentTime = $this->dateTime->gmtDate('Y-m-d H:i:s');
            $bulkModel = $this->bulkImportFactory->create()->load($collection->getImportId());
            $bulkModel->setStatus(1);
            $bulkModel->setProcessedAt($currentTime);
            if ($url) {
                $bulkModel->setReportUrl($url);
            }
            $bulkModel->save();
        }
    }

    /**
     * @return false
     */
    public function checkIfSheetAvailable() {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect(['import_id','file_name']);
        $collection->addFieldToFilter('status', ['eq' => 0]);
        if ($collection->count() > 0) {
            return $collection->getFirstItem();
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
            'email_id' => $subOrderId,
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
        $filePath = 'cat/store-credit/report/';
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

    public function removeOldBulkRecords() {
        $value = $this->_scopeConfig->getValue(self::OLD_BULK_RECORD_REMOVE_DATE);
        if(!empty($value) && $value > 0) {
            $currentTime = $this->dateTime->gmtDate('Y-m-d H:i:s');
            $finalDate = date('Y-m-d H:i:s', strtotime($currentTime . "-".$value." day"));
            $collection = $this->collectionFactory->create();
            $collection->addFieldToSelect(['import_id','file_name', 'report_url']);
            $collection->addFieldToFilter('status', ['eq' => 1]);
            $collection->addFieldToFilter('created_at', ['lteq' => $finalDate]);
            if($collection->getSize() > 0) {
                foreach ($collection as $item) {
                    $filePath = 'cat/store-credit/report/';
                    if ($item->getReportUrl()) {
                        $reportFileName = explode('/', $item->getReportUrl());
                        $reportTarget = $this->mediaDirectory->getAbsolutePath($filePath).end($reportFileName);
                        $this->removeFile($reportTarget);
                    }
                    $fileTarget = $this->mediaDirectory->getAbsolutePath('cat/store-credit/').$item->getFileName();
                    $this->removeFile($fileTarget);
                    $item->delete();
                }
            }
        }
    }

    /**
     * @param string $filepath
     * @return void
     */
    public function removeFile($filepath)
    {
        if ($this->file->isExists($filepath)) {
            $this->file->deleteFile($filepath);
        }
    }
}