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

use Magento\Framework\Locale\Resolver;

/**
 * Description of Edit
 *
 * @author Rocket Bazaar Core Team
 */
class Edit extends \Magedelight\User\Controller\Sellerhtml\User
{

    public function execute()
    {
        if (!$this->vendorHelper->getConfigValue('vendorauthorization/general/enable')) {
            return $this->_redirect('rbvendor/index/index');
        }
        $this->design->applyVendorDesign();
        $resultPage = $this->resultPageFactory->create();
        $vendor = $this->_initVendor();
        try {
            if ($this->getRequest()->getParam('user_id', false)) {
                $vendor->setInterfaceLocale(Resolver::DEFAULT_LOCALE);
                
                if (!$vendor->getId()) {
                    $this->messageManager->addError(__('This user no longer exists.'));
                    $this->_redirect('user/*/');
                    return;
                }
            } else {
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while edit the user.'));
            $this->_redirect('*/*/');
        }

        if ($vendor->getId()) {
            $breadCrumb = __('Edit User');
        } else {
            $breadCrumb = __('New User');
        }

        $resultPage->getConfig()->getTitle()->set(__($breadCrumb));

        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::main');
    }
}
