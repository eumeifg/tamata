<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model\Config\Source\Order;

class CancelledBy implements \Magento\Framework\Data\OptionSourceInterface
{
    const MERCHANT = 'merchant';

    const SELLER = 'seller';

    const BUYER = 'buyer';

    /**
     * @var array
     */
    protected $cancelledByEntities = [];

    public function __construct()
    {
        $this->cancelledByEntities = [
            self::MERCHANT => __('Merchant'),
            self::SELLER => __('Seller'),
            self::BUYER => __('Buyer')
        ];
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return $this->cancelledByEntities;
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
