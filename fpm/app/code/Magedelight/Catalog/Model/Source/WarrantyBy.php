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
 * Description of WarrantyBy
 *
 * @author Rocket Bazaar Core Team
 */
class WarrantyBy implements \Magento\Framework\Data\OptionSourceInterface
{

    const WARRANTY_BY_MANUFACTURER     = 1;
    const WARRANTY_BY_VENDOR    = 2;


    /**
     * WarrantyBy options
     * @var array
     */
    protected $options = [];

    public function __construct()
    {
        $this->options = [
            self::WARRANTY_BY_MANUFACTURER => __('Manufacturer'),
            self::WARRANTY_BY_VENDOR => __('Vendor'),
        ];
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return $this->options;
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
     * @return available options array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
