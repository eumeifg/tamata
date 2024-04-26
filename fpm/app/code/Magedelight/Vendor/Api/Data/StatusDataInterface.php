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

namespace Magedelight\Vendor\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Vendor Business interface.
 * @api
 */
interface StatusDataInterface
{

    const CURRENT_STATUS = 'current_status';

    /**
     * Get vendor id
     * @return string
     */
    public function getCurrentStatus();

    /**
     * Set vendor id
     * @param string $status
     * @return $this
     */
    public function setCurrentStatus($status);
}
