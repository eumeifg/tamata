<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\Config\Source\DefaultVendor;

use Magento\Store\Model\ScopeInterface;

class Criteria implements \Magento\Framework\Option\ArrayInterface
{

    const PRECEDENCE_1_XML_PATH = 'vendor_product/precedence/precedence_1';
    const PRECEDENCE_2_XML_PATH = 'vendor_product/precedence/precedence_2';
    const PRECEDENCE_3_XML_PATH = 'vendor_product/precedence/precedence_3';
    const DEFAULT_PRECEDENCE_XML_PATH = 'vendor_product/precedence/default_precedence';
    
    const HIGHEST_RATING_CRITERIA = 'highest_rating';
    const LEAST_PRICE_CRITERIA = 'least_price';
    const HIGHEST_SELLING_CRITERIA = 'highest_selling';
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
    
    /**
     *
     * @param boolean $addEmpty
     * @return array
     */
    protected function getOptions($addEmpty = true)
    {
        $options = [];
        
        if ($addEmpty) {
            $options[] = ['value' => '', 'label' => __('None')];
        }
        $options[] = ['value' => self::HIGHEST_RATING_CRITERIA, 'label' => __('Highest Rating')];
        $options[] = ['value' => self::LEAST_PRICE_CRITERIA, 'label' => __('Least Price')];
        $options[] = ['value' => self::HIGHEST_SELLING_CRITERIA, 'label' => __('Highest Selling')];
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray($addEmpty = true)
    {
        $options = [];
        
        if ($addEmpty) {
            $options[''] = __('None');
        }
        $options[self::HIGHEST_RATING_CRITERIA] = __('Highest Rating');
        $options[self::LEAST_PRICE_CRITERIA] = __('Least Price');
        $options[self::HIGHEST_SELLING_CRITERIA] = __('Highest Selling');
        return $options;
    }
    
    /**
     * @param boolean $includeDefaultPrecedence
     * @return array
     */
    public function getActiveCriteriasForDefaultVendor($includeDefaultPrecedence = true)
    {
        $activeCriterias = [];
        
        if ($this->getConfigValue(self::PRECEDENCE_1_XML_PATH)) {
            $activeCriterias[] = $this->getConfigValue(self::PRECEDENCE_1_XML_PATH);
        }
        if ($this->getConfigValue(self::PRECEDENCE_2_XML_PATH)) {
            $activeCriterias[] = $this->getConfigValue(self::PRECEDENCE_2_XML_PATH);
        }
        if ($this->getConfigValue(self::PRECEDENCE_3_XML_PATH)) {
            $activeCriterias[] = $this->getConfigValue(self::PRECEDENCE_3_XML_PATH);
        }
        if (empty($activeCriterias) && $includeDefaultPrecedence) {
            $activeCriterias[] = $this->getConfigValue(self::DEFAULT_PRECEDENCE_XML_PATH);
        }
        
        return array_unique($activeCriterias);
    }
    
    /**
     *
     * @param string $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
