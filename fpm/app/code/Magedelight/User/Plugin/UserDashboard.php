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
namespace Magedelight\User\Plugin;

use Magedelight\Vendor\Block\Sellerhtml\Account\Dashboard;

class UserDashboard
{
    /**
     *
     * @param \Magedelight\User\Api\UserAccountManagementInterface $userAccountManagement
     */
    public function __construct(
        \Magedelight\User\Api\UserAccountManagementInterface $userAccountManagement
    ) {
        $this->userAccountManagement = $userAccountManagement;
    }
    
    public function afterGetVendorDashboard(\Magedelight\Vendor\Block\Sellerhtml\Account\Dashboard $subject, $result)
    {
        $result['user'] = $this->userAccountManagement->vendorDashboard();
        return $result;
    }
}
