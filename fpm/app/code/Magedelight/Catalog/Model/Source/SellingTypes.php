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
namespace Magedelight\Catalog\Model\Source;

/**
 * Description of SellingTypes
 *
 * @author Rocket Bazaar Core Team
 */
class SellingTypes implements \Magento\Framework\Option\ArrayInterface
{
    const SELLING_TYPES_NOT_APPLICABLE = 0;
    const SELLING_TYPES_WHOLESALE_DISTRIBUTION = 1;
    const SELLING_TYPES_BRICK_MORTAR_SHOP = 2;
    const SELLING_TYPES_OTHER_ONLINE_MARKETPLACES = 3;
    const SELLING_TYPES_BRAND_RETAIL_WEBSITE = 4;
    
    /**
     * Vendor status options
     * @var array
     */
    protected $statuses = [];

    public function __construct()
    {
        $this->statuses = [
            self::SELLING_TYPES_NOT_APPLICABLE => __('Not Applicable'),
            self::SELLING_TYPES_WHOLESALE_DISTRIBUTION => __('Wholesale Distribution'),
            self::SELLING_TYPES_BRICK_MORTAR_SHOP => __('Brick & Mortar Shop'),
            self::SELLING_TYPES_OTHER_ONLINE_MARKETPLACES => __('Other Online Marketplaces'),
            self::SELLING_TYPES_BRAND_RETAIL_WEBSITE => __('Brand Retail Website')
        ];
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return $this->statuses;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];
        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     *
     * @return available status aarray
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
