<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Setup;

/**
 * Class to generate random backend URI
 *
 */
class VendorFrontnameGenerator
{
    /**
     * Prefix for admin area path
     */
    const VENDOR_AREA_PATH_PREFIX = 'seller';

    /**
     * Length of the backend frontname random part
     */
    const VENDOR_AREA_PATH_RANDOM_PART_LENGTH = 6;

    /**
     * Generate Backend name
     *
     * @return string
     */
    public static function generate()
    {
        return self::VENDOR_AREA_PATH_PREFIX;
    }
}
