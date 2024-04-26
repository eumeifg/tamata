<?php

namespace MDC\Sales\Model;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Config\ScopeConfigInterface;
use MDC\Sales\Model\ResourceModel\BulkInvoiceShip\CollectionFactory;

class DeleteOldRecords
{
    const OLD_BULK_RECORD_REMOVE_DATE = 'sales/cron_config/remove_tat';

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
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * DeleteOldRecords constructor.
     * @param File $file
     * @param DateTime $dateTime
     * @param Filesystem $filesystem
     * @param ScopeConfigInterface $scopeInterface
     * @param CollectionFactory $collectionFactory
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        File $file,
        DateTime $dateTime,
        Filesystem $filesystem,
        ScopeConfigInterface $scopeInterface,
        CollectionFactory $collectionFactory
    ) {
        $this->file = $file;
        $this->dateTime = $dateTime;
        $this->_filesystem = $filesystem;
        $this->_scopeConfig = $scopeInterface;
        $this->collectionFactory = $collectionFactory;
        $this->mediaDirectory = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    public function removeOldBulkRecords() {
        $value = $this->_scopeConfig->getValue(self::OLD_BULK_RECORD_REMOVE_DATE);
        if(!empty($value) && $value > 0) {
            $currentTime = $this->dateTime->gmtDate('Y-m-d H:i:s');
            $finalDate = date('Y-m-d H:i:s', strtotime($currentTime . "-".$value." day"));
            $collection = $this->collectionFactory->create();
            $collection->addFieldToSelect(['bulk_import_id','file_name', 'report_url']);
            $collection->addFieldToFilter('status', ['eq' => 1]);
            $collection->addFieldToFilter('created_at', ['lteq' => $finalDate]);

            if($collection->getSize() > 0) {
                foreach ($collection as $item) {
                    $filePath = 'cat/bulk_invoice_ship/report/';
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
}