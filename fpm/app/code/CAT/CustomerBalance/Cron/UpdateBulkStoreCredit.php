<?php

namespace CAT\CustomerBalance\Cron;

use CAT\CustomerBalance\Model\UpdateStoreCredit;

class UpdateBulkStoreCredit
{
    /**
     * @var UpdateStoreCredit
     */
    protected $updateStoreCredit;

    /**
     * UpdateBulkStoreCredit constructor.
     * @param UpdateStoreCredit $updateStoreCredit
     */
    public function __construct(
        UpdateStoreCredit $updateStoreCredit
    ) {
        $this->updateStoreCredit = $updateStoreCredit;
    }

    /**
     *
     */
    public function updateBulkStoreCredit()
    {
        $logger = $this->getLogger('bulk_store_credit');
        $logger->info('started.......');
        $this->updateStoreCredit->updateStoreCredit($logger);
        $this->updateStoreCredit->removeOldBulkRecords();
        $logger->info('completed.......');
    }

    /**
     * @param $fileName
     * @return \Zend\Log\Logger
     */
    public function getLogger($fileName) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/'.$fileName.'.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;
    }
}