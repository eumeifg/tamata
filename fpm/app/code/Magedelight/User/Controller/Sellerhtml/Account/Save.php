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

use Magento\Framework\Exception\State\UserLockedException;
use Magento\Security\Model\SecurityCookie;

class Save extends \Magedelight\User\Controller\Sellerhtml\User
{

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface
     */
    protected $vendorWebsiteRepository;

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
     */
    protected $vendorWebsite;

    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\User\Model\UserFactory $userFactory
     * @param \Magedelight\Vendor\Model\Design $design
     * @param \Magedelight\Vendor\Model\VendorRepository $vendorRepository
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\User\Model\UserFactory $userFactory,
        \Magedelight\Vendor\Model\Design $design,
        \Magedelight\Vendor\Model\VendorRepository $vendorRepository,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite,
        \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository
    ) {
        $this->storeManager = $storeManager;
        $this->vendorFactory = $vendorFactory;
        $this->vendorWebsite = $vendorWebsite;
        $this->vendorWebsiteRepository = $vendorWebsiteRepository;
        parent::__construct(
            $context,
            $resultPageFactory,
            $coreRegistry,
            $userFactory,
            $design,
            $vendorRepository,
            $vendorHelper,
            $authSession
        );
    }
    
    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $storeId = $this->storeManager->getStore()->getId();
        $this->getRequest()->setParam('user_id', $this->authSession->getUser()->getId());
        $vendorId = (int)$this->getRequest()->getParam('user_id');
        if (!$data) {
            $this->_redirect('rbuser/*/');
            return;
        }
        
        $vendorWebsite = $this->vendorWebsiteRepository->getVendorWebsiteData($vendorId);
        $vendorWebsite->setName($data['name']);
        $vendorWebsite->save();
           
        // Add Entry into md_vendor table data email, mobile number, website id
        // Add Entry into md_vendor_website_data table data vendor id, website id, store id,
        
        /** @var $model \Magedelight\Vendor\Model\Vendor */
        $model = $this->_initVendor();
    
        if ($vendorId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This user no longer exists.'));
            $this->_redirect('rbuser/*/');
            return;
        }
        
        $errors = [];
        $user = new \Magento\Framework\DataObject($data);
        if (\Zend_Validate::is($user->getPassword(), 'NotEmpty')) {
            if (!\Zend_Validate::is($user->getPassword(), 'Regex', ['pattern'=>'/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=]).{6,30}$/'])) {
                $errors[] = __('"%fieldName" must be at least 6 characters and no more than 30 characters, also it must include alphanumeric lower and upper case letters with at least one special character.', ['fieldName' => 'Password']);
            } elseif (!\Zend_Validate::is($user->getPasswordConfirmation(), 'NotEmpty')) {
                $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'Confirm password']);
            } else {
                if ($user->getPassword() != $user->getPasswordConfirmation()) {
                    $errors[] = __('Password and the confirm password field does not the have same value.');
                }
            }
            $model->setData('password', $user->getPassword());
        }
        
        if (count($errors) > 0) {
            $this->messageManager->addWarning(implode(', ', $errors));
            $this->_redirect('*/*/*');
            return false;
        }
        
        $model->setData('mobile', $user->getMobile());
        
        try {
            $this->vendorRepository->save($model);
            $this->messageManager->addSuccess(__('You saved the user.'));
            $this->_redirect('rbuser/*/');
        } catch (UserLockedException $e) {
            $this->_redirect('rbuser/*/');
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $this->messageManager->addError(__('You have entered an invalid password for current user.'));
            $this->_redirect('*/*/*');
        } catch (\Magento\Framework\Validator\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->_redirect('*/*/*');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(__('You have entered an invalid password for current user.'));
            $this->_redirect('*/*/*');
        }
    }

    /**
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::account');
    }
}
