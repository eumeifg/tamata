<?php

namespace MDC\Sales\Cron;

use MDC\Sales\Model\DeleteOldRecords;

class DeleteBulkImport
{
    /**
     * @var DeleteOldRecords
     */
    protected $deleteOldRecords;

    /**
     * DeleteBulkImport constructor.
     * @param DeleteOldRecords $deleteOldRecords
     */
    public function __construct(
        DeleteOldRecords $deleteOldRecords
    ) {
        $this->deleteOldRecords = $deleteOldRecords;
    }

    public function deleteOldRecords()
    {
        $this->deleteOldRecords->removeOldBulkRecords();
    }
}