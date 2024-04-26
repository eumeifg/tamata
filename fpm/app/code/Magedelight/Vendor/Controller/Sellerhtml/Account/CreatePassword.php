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
namespace Magedelight\Vendor\Controller\Sellerhtml\Account;

use Magedelight\Vendor\Api\AccountManagementInterface;
use Magento\Framework\View\Result\PageFactory;
use Magedelight\Backend\App\Action\Context;

/**
 * @author Rocket Bazaar Core Team
 * Created at 16 March, 2016 3:55 PM
 */
class CreatePassword extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;
    
    /** @var AccountManagementInterface */
    protected $accountManagement;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magedelight\Vendor\Model\Design $design
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magedelight\Vendor\Model\Design $design,
        AccountManagementInterface $accountManagement
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        $this->design = $design;
        parent::__construct($context);
    }

    /**
     * Resetting password handler
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resetPasswordToken = (string)$this->getRequest()->getParam('token');
        $vendorId = (int)$this->getRequest()->getParam('id');
        $isDirectLink = $resetPasswordToken != '' && $vendorId != 0;
        if (!$isDirectLink) {
            $resetPasswordToken = (string)$this->_session->getRpToken();
            $vendorId = (int)$this->_session->getRpVendorId();
        }

        try {
            $this->accountManagement->validateResetPasswordLinkToken($vendorId, $resetPasswordToken);

            if ($isDirectLink) {
                $this->_session->setRpToken($resetPasswordToken);
                $this->_session->setRpVendorId($vendorId);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/account/createpassword');
                return $resultRedirect;
            } else {
                $this->design->applyVendorDesign();
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getLayout()->getBlock('resetPassword')->setVendorId($vendorId)
                    ->setResetPasswordLinkToken($resetPasswordToken);
                return $resultPage;
            }
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Your password reset link has expired.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/account/forgotpassword');
            return $resultRedirect;
        }
    }
    
    protected function _isAllowed()
    {
        return true;
    }
}
