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

/**
 * Vendor Login interface.
 * @api
 */
interface LoginDataInterface
{

    const MOBILE = 'mobile';
    const EMAIL = 'email';

    /**
     * Get vendor Mobile
     * @return string
     */
    public function getMobile();

    /**
     * Set vendor mobile
     * @param string $mobile
     * @return $this
     */
    public function setMobile($mobile);

    /**
     * Get vendor Mobile
     * @return string
     */
    public function getEmail();

    /**
     * Set vendor email
     * @param string $email
     * @return $this
     */
    public function setEmail($email);
}
