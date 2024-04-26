<?php

namespace CAT\Custom\Cron;

use CAT\Custom\Model\Entity\StoreCredit;
use CAT\Custom\Helper\Automation as AutomationHelper;
use CAT\Custom\Model\Source\Option;

class UpdateStoreCredit
{
    /**
     * @var StoreCredit
     */
    protected $storeCredit;

    /**
     * @var AutomationHelper
     */
    protected $automationHelper;

    /**
     * @param StoreCredit $storeCredit
     * @param AutomationHelper $automationHelper
     */
    public function __construct(
        StoreCredit $storeCredit,
        AutomationHelper $automationHelper
    ) {
        $this->storeCredit = $storeCredit;
        $this->automationHelper = $automationHelper;
    }

    public function updateBulkStoreCredit()
    {
        if($this->automationHelper->getEntityAutomationEnable(Option::STORE_CREDIT_KEYWORD)) {
            $logger = $this->automationHelper->getLogger(Option::STORE_CREDIT_KEYWORD);
            $logger->info('started.......');
            $this->storeCredit->updateStoreCredit($logger);
            //$this->automationHelper->removeOldBulkRecords(Option::STORE_CREDIT_KEYWORD);
            $logger->info('completed.......');
        }
    }
}