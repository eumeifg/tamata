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

namespace Magedelight\Vendor\Model\Profile;

use Magedelight\Vendor\Api\Data\LoginDataInterface;
use Magento\Framework\DataObject;

/**
 * Vendor Login Data interface.
 * @api
 */
class LoginData extends DataObject implements LoginDataInterface
{
    /**
     * {@inheritDoc}
     */
    public function getMobile()
    {
        return $this->getData(self::MOBILE);
    }

    /**
     * {@inheritDoc}
     */
    public function setMobile($mobile)
    {
        return $this->setData(self::MOBILE, $mobile);
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * {@inheritDoc}
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }
}
