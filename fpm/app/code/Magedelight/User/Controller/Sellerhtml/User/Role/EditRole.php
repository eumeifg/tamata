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
namespace Magedelight\User\Controller\Sellerhtml\User\Role;

class EditRole extends \Magedelight\User\Controller\Sellerhtml\User\Role
{

    public function execute()
    {
        $this->design->applyVendorDesign();
        $resultPage = $this->resultPageFactory->create();

        $role = $this->_initRole();
        if ($role->getId()) {
            $breadCrumb = __('Edit Role');
            $breadCrumbTitle = __('Edit Role');
        } else {
            $breadCrumb = __('Add New Role');
            $breadCrumbTitle = __('Add New Role');
        }

        $resultPage->getConfig()->getTitle()->set(__($breadCrumb));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::main');
    }
}
