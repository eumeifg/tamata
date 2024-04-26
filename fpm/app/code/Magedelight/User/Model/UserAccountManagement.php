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
namespace Magedelight\User\Model;

use Magedelight\User\Api\UserAccountManagementInterface;

class UserAccountManagement implements UserAccountManagementInterface
{
    protected $currentVendor = null;
    
    protected $currentVendorResources = [];
    
    /**
     * @param \Magedelight\User\Model\User $usersModel
     */
    public function __construct(
        \Magedelight\User\Model\User $usersModel
    ) {
        $this->usersModel = $usersModel;
    }
    
    
    protected function getCurrentVendorResources()
    {
        if (empty($this->currentVendorResources)) {
            $this->currentVendorResources = $this->usersModel->getAllowedResourcesByRole();
        }
        return $this->currentVendorResources;
    }
    
    protected function isAllowedAllResources()
    {
        return $this->getCurrentVendorResources();
    }
    
    /**
     * @return $this
     *
     */
    public function vendorDashboard()
    {
        /*
         * isFinancialSectionAllowed
         */
        $result['isFinancialSectionAllowed'] = false;
        if ($this->isAllowedAllResources() === true) {
            $result['isFinancialSectionAllowed'] = true;
        } elseif (in_array("Magedelight_Vendor::financial", $this->getCurrentVendorResources())) {
            $result['isFinancialSectionAllowed'] = true;
        }
        
        
        /*
         * isProductSectionAllowed
         */
        $result['isProductSectionAllowed'] = false;
        if ($this->isAllowedAllResources() === true) {
            $result['isProductSectionAllowed'] = true;
        } elseif (in_array("Magedelight_Catalog::manage_products", $this->getCurrentVendorResources())) {
            $result['isProductSectionAllowed'] = true;
        }
        
        /*
         * isOrderSectionAllowed
         */
        $result['isOrderSectionAllowed'] = false;
        if ($this->isAllowedAllResources() === true) {
            $result['isOrderSectionAllowed'] = true;
        } elseif (in_array("Magedelight_Sales::manage_orders", $this->getCurrentVendorResources())) {
            $result['isProductSectionAllowed'] = true;
        }
        
        return $result;
    }
}
