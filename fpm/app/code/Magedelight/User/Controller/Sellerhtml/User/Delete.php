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
namespace Magedelight\User\Controller\Sellerhtml\User;

class Delete extends \Magedelight\User\Controller\Sellerhtml\User
{
    public function execute()
    {
        $vendor = $this->_initVendor();
        try {
            $vendor->delete();
            $this->messageManager->addSuccess(__('You have deleted user successfully .'));
            $this->_redirect('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while deleting the user.'));
            $this->_redirect('*/*/');
        }
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::main');
    }
}
