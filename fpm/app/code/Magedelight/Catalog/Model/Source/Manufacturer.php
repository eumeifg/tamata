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
 * Description of Manufacturer
 *
 * @author Rocket Bazaar Core Team
 */
class Manufacturer implements \Magento\Framework\Option\ArrayInterface
{
    const MANUFACTURE_PRODUCTS_YES = 0;
    const MANUFACTURE_AUTHORIZED_BRAND = 1;
    const MANUFACTURE_USE_PRIVATE_LABEL = 2;
    
    /**
     * Vendor status options
     * @var array
     */
    protected $statuses = [];

    public function __construct()
    {
        $this->statuses = [
            self::MANUFACTURE_PRODUCTS_YES => __('Yes, we manufacture the products'),
            self::MANUFACTURE_AUTHORIZED_BRAND => __('No, We are authorized distributor/seller for this brand'),
            self::MANUFACTURE_USE_PRIVATE_LABEL => __('No, We procure it from open market
             and use our own private label')
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
