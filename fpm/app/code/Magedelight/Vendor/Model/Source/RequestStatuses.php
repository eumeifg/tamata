<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model\Source;

class RequestStatuses implements \Magento\Framework\Option\ArrayInterface
{

    const VENDOR_REQUEST_STATUS_PENDING = 0;
    const VENDOR_REQUEST_STATUS_APPROVED = 1;
    const VENDOR_REQUEST_STATUS_REJECTED = 2;
    
    /**
     * Vendor status options
     * @var array
     */
    protected $statuses = [];

    public function __construct()
    {
        $this->statuses = [
            self::VENDOR_REQUEST_STATUS_PENDING => __('Pending'),
            self::VENDOR_REQUEST_STATUS_APPROVED => __('Approved'),
            self::VENDOR_REQUEST_STATUS_REJECTED => __('Rejected')
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
