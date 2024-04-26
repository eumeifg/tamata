<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PrebookStatus
 */
class EmailStatus implements OptionSourceInterface
{

    const MAIL_SENT = 1;

    const SENDING_ERROR = 2;

    const CANCELED = 3;

    const BLACKLISTED = 4;

    const SOME_OUTSTOCK = 5;

    const ALL_OUTSTOCK = 6;

    const ORDERED = 7;

    const RESTORED_ALREADY = 8;

    const CART_UPDATED = 9;

    const MAIL_PENDING=10;

    const DELETED=11;

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::MAIL_SENT         => __('Sent'),
            self::SENDING_ERROR     => __('Failed'),
            self::CANCELED          => __('Cancelled'),
            self::BLACKLISTED       => __('Blacklisted'),
            self::SOME_OUTSTOCK     => __('Some Product(s) OutStock'),
            self::ALL_OUTSTOCK      => __('All Product(s) OutStock'),
            self::ORDERED           => __('Ordered'),
            self::RESTORED_ALREADY  => __('Cart Was Already Restored'),
            self::CART_UPDATED      => __('New Cart Was Created'),
            self::MAIL_PENDING      => __('Pending'),
            self::DELETED      => __('Deleted')
        ];
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $res = [];
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }
}
