<?php

namespace CAT\Custom\Cron;

use CAT\Custom\Model\Source\Option;
use CAT\Custom\Model\Entity\InvoiceShipment;
use CAT\Custom\Helper\Automation as AutomationHelper;

class UpdateInvoiceShipment {

    protected $invoiceShipment;

    protected $automationHelper;

    public function __construct(
        InvoiceShipment $invoiceShipment,
        AutomationHelper $automationHelper
    ) {
        $this->invoiceShipment = $invoiceShipment;
        $this->automationHelper = $automationHelper;
    }

    public function updateBulkInvoiceShipment() {
        if($this->automationHelper->getEntityAutomationEnable(Option::INVOICE_SHIPMENT_KEYWORD)) {
            $logger = $this->automationHelper->getLogger(Option::INVOICE_SHIPMENT_KEYWORD);
            $logger->info('started.......');
            $this->invoiceShipment->createInvoiceAndShipment($logger);
            $this->automationHelper->removeOldBulkRecords(Option::INVOICE_SHIPMENT_KEYWORD);
            $logger->info('completed.......');
        }
    }
}