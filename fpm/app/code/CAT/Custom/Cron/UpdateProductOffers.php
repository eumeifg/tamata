<?php

namespace CAT\Custom\Cron;

use CAT\Custom\Model\ProductOffersUpdate;
use CAT\Custom\Helper\Automation;
use CAT\Custom\Model\Source\Option;

class UpdateProductOffers
{
    /**
     * @var ProductOffersUpdate
     */
    protected $offersUpdate;

    /**
     * @var Automation
     */
    protected $automationHelper;

    /**
     * @param ProductOffersUpdate $offersUpdate
     * @param Automation $automationHelper
     */
    public function __construct(
        ProductOffersUpdate $offersUpdate,
        Automation $automationHelper
    ) {
        $this->offersUpdate = $offersUpdate;
        $this->automationHelper = $automationHelper;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateProductOffers()
    {
        if($this->automationHelper->getEntityAutomationEnable(Option::PRODUCT_OFFERS_KEYWORD)){
            $this->offersUpdate->productOffersUpdate();
        }
    }
}
