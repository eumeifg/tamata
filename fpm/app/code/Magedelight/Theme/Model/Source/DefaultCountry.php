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

class DefaultCountry implements \Magento\Framework\Option\ArrayInterface
{
    const XML_PATH_GENERAL_COUNTRY_DEFAULT = 'general/country/default';
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
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

    public function toOptionArray()
    {
        $options = [];
        $options[] =  ['value' => '', 'label' => ''];
        $options[] =  ['value' => $this->getDefaultCountry(), 'label' => __($this->getDefaultCountryLabel())];
        
        return $options;
    }
    
    public function getDefaultCountry()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_GENERAL_COUNTRY_DEFAULT, $storeScope);
    }
    
    /**
     * get default country label
     * @return string
     */
    public function getDefaultCountryLabel()
    {
        return $this->countryFactory->create()->loadByCode($this->getDefaultCountry())->getName();
    }
}
