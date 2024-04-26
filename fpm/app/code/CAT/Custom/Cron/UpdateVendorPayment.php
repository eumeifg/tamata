<?php

namespace CAT\Custom\Cron;

use CAT\Custom\Model\Entity\VendorPayment;
use CAT\Custom\Helper\Automation as AutomationHelper;
use CAT\Custom\Model\Source\Option;

class UpdateVendorPayment 
{
    /**
     * @var VendorPayment
     */
    protected $vendorPayment;

    /**
     * @var AutomationHelper
     */
    protected $automationHelper;

    /**
     * @param VendorPayment $vendorPayment
     * @param AutomationHelper $automationHelper
     */
    public function __construct(
        VendorPayment $vendorPayment,
        AutomationHelper $automationHelper
    ) {
        $this->vendorPayment = $vendorPayment;
        $this->automationHelper = $automationHelper;
    }

    public function updateBulkVendorPayment()
    {
        if($this->automationHelper->getEntityAutomationEnable(Option::VENDOR_PAYMENT_STATUS)) {
            $logger = $this->automationHelper->getLogger(Option::VENDOR_PAYMENT_STATUS);
            $logger->info('started.......');
            $this->vendorPayment->updateVendorPaymentToPaid($logger);
            //$this->automationHelper->removeOldBulkRecords(Option::VENDOR_PAYMENT_STATUS);
            $logger->info('completed.......');
        }
    }
}