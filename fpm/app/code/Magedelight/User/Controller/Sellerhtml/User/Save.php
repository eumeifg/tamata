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
        \Magedelight\Backend\Model\Auth\Session $authSession
    ) {
        $this->storeManager = $storeManager;
        $this->vendorFactory = $vendorFactory;
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
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $vendorId = (int)$this->getRequest()->getParam('user_id');
        
        if (!$data) {
            $this->_redirect('rbuser/*/');
            return;
        }
           
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
        } else {
            if (!$model->getId()) {
                $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'Password']);
            }
        }
        
        if (count($errors) > 0) {
            $this->messageManager->addWarning(implode(', ', $errors));
            $this->_redirect('*/*/*');
            return false;
        }
        
        $model->setData('name', $user->getName());
        $model->setData('email', $user->getEmail());
        $model->setData('mobile', $user->getMobile());
        $model->setData('website_id', $websiteId);
        $model->setData('parent_vendor_id', $user->getParentVendorId());
        
        if (!$data['user_id']) {
            if ($this->getEmailVarify($data['email'])) {
                $this->messageManager->addWarning('Email is already in existed, please use another one.');
                $this->redirectToEdit($model, $data);
                return false;
            }
        } else {
            if ($this->getEmailVarifyForEdit($data['email'], $data['user_id'])) {
                $this->messageManager->addWarning('Email is already in existed, please use another one.');
                $this->redirectToEdit($model, $data);
                return false;
            }
        }

        try {
            $this->vendorRepository->save($model);
            $eventParams = ['controller' => $this, 'vendor' => $model,'post_data' => $data, 'status'=> $data['status']];
            $this->_eventManager->dispatch('vendor_user_create_success', $eventParams);
            $this->messageManager->addSuccess(__('You saved the user.'));
            $this->_redirect('rbuser/*/');
        } catch (UserLockedException $e) {
            $this->_redirect('rbuser/*/');
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $this->messageManager->addError(__('You have entered an invalid password for current user.'));
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Validator\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(__('You have entered an invalid password for current user.'));
            $this->redirectToEdit($model, $data);
        }
    }

    /**
     * Used for email validation while create new user
     * @param string $email
     * @return boolean
     */
    protected function getEmailVarify($email)
    {
        $collection = $this->vendorFactory->create()->getCollection();
        $collection->addFieldToFilter('email', ['eq'=>$email]);
        if ($collection->count()) {
            return true;
        }
        return false;
    }

    /**
     * Used for email validation while edit the user
     * @param string $email
     * @param int $userId
     * @return boolean
     */
    public function getEmailVarifyForEdit($email, $userId)
    {
        $collection = $this->vendorFactory->create()->getCollection();
        $collection->addFieldToFilter('email', ['eq'=>$email]);
        $collection->addFieldToFilter('vendor_id', ['neq'=>$userId]);
        if ($collection->count()) {
            return true;
        }
        return false;
    }
    

    /**
     * @param \Magento\User\Model\User $model
     * @param array $data
     * @return void
     */
    protected function redirectToEdit(\Magedelight\Vendor\Model\Vendor $model, array $data)
    {
        $arguments = $this->getRequest()->getParam('user_id') ? ['user_id' => $this->getRequest()->getParam('user_id')] : [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        $this->_redirect('*/*/edit', $arguments);
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::main');
    }
}
