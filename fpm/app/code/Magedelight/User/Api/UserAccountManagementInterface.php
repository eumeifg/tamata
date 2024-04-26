<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Api;

/**
 * Interface for managing vendors accounts.
 */
interface UserAccountManagementInterface
{
    /**
     * @api
     * @return array|int|string|bool|float Scalar or array of scalars
     */
    public function vendorDashboard();
}
