<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Source\Customer;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class WhoCanInviteType
 * @package Aheadworks\Raf\Model\Source\Config\General
 */
class WhoCanInviteType implements OptionSourceInterface
{
    /**#@+
     * Constants defined for "who can invite" types
     */
    const ALL_CUSTOMERS = 'all_customers';
    const CUSTOMERS_WITH_PURCHASES = 'customer_with_purchases';
    /**#@-*/

    /**
     * Retrieve "who can invite" types as option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ALL_CUSTOMERS,
                'label' => __('All Registered Customers')
            ],
            [
                'value' => self::CUSTOMERS_WITH_PURCHASES,
                'label' => __('Only Registered Customers with Previous Purchases')
            ],
        ];
    }
}
