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
namespace Magedelight\User\Controller\Sellerhtml\Account;

use Magento\Framework\View\Result\PageFactory;

class Index extends \Magedelight\User\Controller\Sellerhtml\User
{

    /**
     * Default vendor account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->design->applyVendorDesign();
        
        $this->getRequest()->setParam('user_id', $this->authSession->getUser()->getId());
        $vendor = $this->_initVendor();
        try {
            if ($this->getRequest()->getParam('user_id', false)) {
                if (!$vendor->getId()) {
                    $this->messageManager->addError(__('This user no longer exists.'));
                    $this->_redirect('user/*/');
                    return;
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while edit the user.'));
            $this->_redirect('*/*/');
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Profile'));
        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::account');
    }
}
