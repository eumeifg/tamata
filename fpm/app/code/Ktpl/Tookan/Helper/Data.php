<?php

namespace Ktpl\Tookan\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function getStoreName()
    {
        return $this->getConfigValue('general/store_information/name');
    }

    /**
     * @param string $path
     * @return string
     */
    public function getConfigValue(string $path): string
    {
        $value = $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            null
        );

        return $value ?? '';
    }

    /**
     * @return string
     */
    public function getStoreStreetAddress()
    {
        return $this->getConfigValue('general/store_information/street_line1') . " " .
            $this->getConfigValue('general/store_information/street_line2');
    }

    /**
     * @return string
     */
    public function getStoreCity()
    {
        return $this->getConfigValue('general/store_information/city');
    }

    /**
     * @return string
     */
    public function getStoreRegion()
    {
        return $this->getConfigValue('general/store_information/region');
    }

    /**
     * @return string
     */
    public function getStorePincode()
    {
        return $this->getConfigValue('general/store_information/postcode');
    }

    /**
     * @return string
     */
    public function getStoreCountry()
    {
        return $this->getConfigValue('general/store_information/country_id');
    }

    /**
     * @return string
     */
    public function getStoreLatitude()
    {
        return $this->getConfigValue('general/store_information/latitude');
    }

    /**
     * @return string
     */
    public function getStoreLongitude()
    {
        return $this->getConfigValue('general/store_information/longitude');
    }

    /**
     * @return string
     */
    public function getStorePhone()
    {
        return $this->getConfigValue('general/store_information/phone');
    }

    /**
     * @return string
     */
    public function getPickupBuffer()
    {
        return $this->getConfigValue('tookan/general/pickup_buffer') ? : '+4';
    }

    /**
     * @return string
     */
    public function getDeliveryBuffer()
    {
        return $this->getConfigValue('tookan/general/delivery_buffer') ? : '+24';
    }
}
