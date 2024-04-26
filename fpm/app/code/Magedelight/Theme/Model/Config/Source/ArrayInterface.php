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
namespace Magedelight\Theme\Model\Config\Source;

interface ArrayInterface extends \Magento\Framework\Data\OptionSourceInterface
{

    const ADMIN_VALUE = 1;
    const VENDOR_VALUE = 2;
    const CUSTOMER_VALUE = 3;
    const ADMIN_LABEL = 'Admin';
    const VENDOR_LABEL = 'Vendor';
    const CUSTOMER_LABEL = 'Customer';
}
