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

class Delete extends \Magedelight\User\Controller\Sellerhtml\User\Role
{
    public function execute()
    {
        $role = $this->_initRole();
        try {
            $role->delete();
            $this->messageManager->addSuccess(__('You have deleted Role successfully.'));
            $this->_redirect('*/*/role_index');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while deleting the role.'));
            $this->_redirect('*/*/role_index');
        }
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::main');
    }
}
