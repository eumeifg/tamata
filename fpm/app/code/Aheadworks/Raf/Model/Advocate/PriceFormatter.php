<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PriceFormatter
 * @package Aheadworks\Raf\Model\Advocate\Account\Rule\Viewer
 */
class PriceFormatter
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrencyInterface;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param PriceCurrencyInterface $priceCurrencyInterface
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrencyInterface,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->priceCurrencyInterface = $priceCurrencyInterface;
    }

    /**
     * Get formatted percent
     *
     * @param int|float $price
     * @return string
     */
    public function getFormattedPercentPrice($price)
    {
        return __('%1%', $price * 1);
    }

    /**
     * Get formatted fixed price by store
     *
     * @param float $price
     * @param int $storeId
     * @return string
     */
    public function getFormattedFixedPriceByStore($price, $storeId)
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        return $this->getFormattedFixedPriceByWebsite($price, $websiteId);
    }

    /**
     * Get formatted fixed price by website
     *
     * @param float $price
     * @param int $websiteId
     * @return string
     */
    public function getFormattedFixedPriceByWebsite($price, $websiteId)
    {
        try {
            $website = $this->storeManager->getWebsite($websiteId);
            $currencyCode = $website->getBaseCurrencyCode();
        } catch (\Exception $exception) {
            $currencyCode = null;
        }

        return $this->priceCurrencyInterface->format(
            $price,
            false,
            null,
            null,
            $currencyCode
        );
    }
}
