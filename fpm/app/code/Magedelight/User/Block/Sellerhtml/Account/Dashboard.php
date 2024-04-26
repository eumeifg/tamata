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
namespace Magedelight\User\Block\Sellerhtml\Account;

class Dashboard extends \Magedelight\Vendor\Block\Sellerhtml\Account\Dashboard
{

    protected $currentVendor = null;
    
    protected $currentVendorResources = [];
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Vendor\Api\DashboardManagementInterface $dashboardManagement
     * @param \Magedelight\User\Model\User $usersModel
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Vendor\Api\DashboardManagementInterface $dashboardManagement,
        \Magedelight\User\Model\User $usersModel
    ) {
        $this->usersModel = $usersModel;
        parent::__construct($context, $dashboardManagement);
    }
    
    public function getVendor()
    {
        if (!$this->currentVendor) {
            $this->currentVendor = $this->getVendorLoggedInData();
        }
        return $this->currentVendor;
    }
    
    public function isOrderSectionAllowed()
    {
        if ($this->isAllowedAllResources() === true) {
            return true;
        }
        if (in_array("Magedelight_Sales::manage_orders", $this->getCurrentVendorResources())) {
            return true;
        }
        return false;
    }
    
    public function isProductSectionAllowed()
    {
        if ($this->isAllowedAllResources() === true) {
            return true;
        }
        if (in_array("Magedelight_Catalog::manage_products", $this->getCurrentVendorResources())) {
            return true;
        }
        return false;
    }
    
    public function isFinancialSectionAllowed()
    {
        if ($this->isAllowedAllResources() === true) {
            return true;
        }
        if (in_array("Magedelight_Vendor::financial", $this->getCurrentVendorResources())) {
            return true;
        }
        return false;
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
}
