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
namespace Magedelight\Theme\Model\Source;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Region extends Column
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
     *
     * @var Array
     */
    protected $optionsArray = [];
    
    /**
     *
     * @var Array
     */
    protected $options = [];
    
    /**
     *
     * @var integer
     */
    protected $previousCountryId = null;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->countryFactory = $countryFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['region_id']) {
                    $item['region'] = $this->getRegionLabel($item['region_id'], $item['country_id']);
                }
            }
        }
        return $dataSource;
    }
    
    /**
     * get default country label
     * @return string
     */
    public function getDefaultCountryLabel()
    {
        return $this->countryFactory->create()->loadByCode($this->getDefaultCountry())->getName();
    }

    /**
     *
     * @return string
     */
    public function getDefaultCountry()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GENERAL_COUNTRY_DEFAULT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     *
     * @param string $countryCode
     * @return boolean
     */
    public function getIsRegionRequired($countryCode)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $countries = $this->scopeConfig->getValue(self::XML_PATH_GENERAL_REGION_STATE_REQUIRED, $storeScope);
        if ($countries) {
            return in_array($countryCode, explode(',', $countries));
        }
        return false;
    }
    
    /**
     *
     * @param string $countryCode
     * @return boolean
     */
    public function getIsZipRequired($countryCode)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $countries = $this->scopeConfig->getValue(self::XML_PATH_GENERAL_OPTIONAL_ZIP_COUNTRIES, $storeScope);
        if ($countries) {
            return !in_array($countryCode, explode(',', $countries));
        }
        return true;
    }

    /**
     *
     * @param string $id
     * @return array
     */
    protected function getRegionList($id = '')
    {
        /*
         * TODO : Get all region Listing for all country (INDCity module support)
         */
        $countrycode = ($id)?$id:$this->getDefaultCountry();

        $_options = $this->countryFactory->create()
                ->setId($countrycode)
                ->getLoadedRegionCollection()
                ->toOptionArray();
        return $_options;
    }

    /**
     * get options
     * @param string $id
     * @param boolean $isCountryChanged
     * @return array
     */
    public function toOptionArray($id = null, $isCountryChanged = false)
    {
        if (empty($this->optionsArray) || $isCountryChanged) {
            $this->optionsArray = $this->getRegionList($id);
        }
        return $this->optionsArray;
    }

    /**
     * @param array $options
     * @param string $countryId
     * @param boolean $isCountryChanged
     * @return array
     */
    public function getOptions(array $options = [], $countryId = null, $isCountryChanged = false)
    {
        if (empty($this->options) || $isCountryChanged) {
            $countryOptions = $this->toOptionArray($countryId, $isCountryChanged);
            foreach ($countryOptions as $option) {
                $this->options[$option['value']] = $option['label'];
            }
        }
        return $this->options;
    }
    
    /**
     *
     * @param integer $regionId
     * @param string $countryId
     * @return string|NULL
     */
    public function getRegionLabel($regionId, $countryId = null)
    {
        $isCountryChanged = false;
        if (!empty($countryId) && $countryId != $this->previousCountryId) {
            $this->options = [];
            $this->previousCountryId = $countryId;
            $isCountryChanged = true;
        }
        if (empty($this->options)) {
            $this->options = $this->getOptions([], $countryId, $isCountryChanged);
        }
        if (is_array($this->options) && array_key_exists($regionId, $this->options)) {
            return $this->options[$regionId];
        }
        return null;
    }
}
