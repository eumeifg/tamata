<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class PaymentStatus implements OptionSourceInterface
{

    const PAYMENT_STATUS_PENDING = 0;
    const PAYMENT_STATUS_PAID = 1;

    //CONST PAYMENT_STATUS_PARTIAL = 2;

    /**
     * PAyment status options
     * @var array
     */
    protected $statuses = [];

    public function __construct()
    {
        $this->statuses = [
            self::PAYMENT_STATUS_PENDING => __('Pending'),
            self::PAYMENT_STATUS_PAID => __('Processed')
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
        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
