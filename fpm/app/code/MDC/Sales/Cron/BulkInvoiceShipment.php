<?php

namespace MDC\Sales\Cron;

use MDC\Sales\Model\BulkInvoiceShipmentModel;

class BulkInvoiceShipment
{
    /**
     * @var BulkInvoiceShipmentModel
     */
    protected $bulkInvoiceShipmentModel;

    /**
     * BulkInvoiceShipment constructor.
     * @param BulkInvoiceShipmentModel $bulkInvoiceShipmentModel
     */
    public function __construct(
        BulkInvoiceShipmentModel $bulkInvoiceShipmentModel
    ) {
        $this->bulkInvoiceShipmentModel = $bulkInvoiceShipmentModel;
    }

    public function createBulkInvoiceShipment() {
        $this->bulkInvoiceShipmentModel->createBulkInvoiceShipment();
    }
}