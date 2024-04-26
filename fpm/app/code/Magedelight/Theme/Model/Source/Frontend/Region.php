<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Theme
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Theme\Model\Source\Frontend;

class Region
{
    const XML_PATH_GENERAL_COUNTRY_DEFAULT = 'general/country/default';
    const XML_PATH_GENERAL_REGION_STATE_REQUIRED = 'general/region/state_required';
    const XML_PATH_GENERAL_OPTIONAL_ZIP_COUNTRIES = 'general/country/optional_zip_countries';

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Array
     */
    protected $optionsArray;

    /**
     * @var Array
     */
    protected $options;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->countryFactory = $countryFactory;
    }

    /**
     * get default country label
     * @return string
     */
    public function getDefaultCountryLabel()
    {
        return $this->countryFactory->create()->loadByCode($this->getDefaultCountry())->getName();
    }

    public function getDefaultCountry()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_GENERAL_COUNTRY_DEFAULT, $storeScope);
    }

    public function getIsRegionRequired($countryCode)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $countries = $this->scopeConfig->getValue(self::XML_PATH_GENERAL_REGION_STATE_REQUIRED, $storeScope);
        if ($countries) {
            return in_array($countryCode, explode(',', $countries));
        }
        return false;
    }

    public function getIsZipRequired($countryCode)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $countries = $this->scopeConfig->getValue(self::XML_PATH_GENERAL_OPTIONAL_ZIP_COUNTRIES, $storeScope);
        if ($countries) {
            return !in_array($countryCode, explode(',', $countries));
        }
        return true;
    }

    protected function getRegionList($countryCode = null)
    {
        /*
         * TODO : Get all region Listing for all country (INDCity module support)
         */
        if (!$countryCode) {
            $countryCode = $this->getDefaultCountry();
        }

        $_options = $this->countryFactory->create()
                ->setId($countryCode)
                ->getLoadedRegionCollection()
                ->toOptionArray();
        return $_options;
    }

    /**
     * get options
     *
     * @return array
     */
    public function toOptionArray($countryCode = null)
    {
        if ($this->optionsArray === null) {
            $this->optionsArray = $this->getRegionList($countryCode);
        }
        return $this->optionsArray;
    }

    /**
     * @param array $options
     * @return array
     */
    public function getOptions($countryCode = null)
    {
        if (empty($this->options)) {
            $countryOptions = $this->toOptionArray($countryCode);
            foreach ($countryOptions as $option) {
                $this->options[$option['value']] = $option['label'];
            }
        }
        return $this->options;
    }

    public function getRegionLabel($regionId, $countryCode = null)
    {
        if (empty($this->options)) {
            $this->options = $this->getOptions($countryCode);
        }
        return $this->options[$regionId];
    }
}
