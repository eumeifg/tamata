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

use Magento\Framework\Controller\ResultFactory;

/**
 * Description of Save
 *
 * @author Rocket Bazaar Core Team
 */
class Save extends \Magedelight\User\Controller\Sellerhtml\User\Role
{

    /**
     * @var \Magedelight\Authorization\Acl\RootResource
     */
    protected $rootResource;


    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magedelight\Vendor\Model\Design $design
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Authorization\Model\RoleFactory $roleFactory
     * @param \Magedelight\User\Model\UserFactory $userFactory
     * @param \Magedelight\Authorization\Model\RulesFactory $rulesFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magedelight\Authorization\Acl\RootResource $rootResource
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Vendor\Model\Design $design,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Authorization\Model\RoleFactory $roleFactory,
        \Magedelight\User\Model\UserFactory $userFactory,
        \Magedelight\Authorization\Model\RulesFactory $rulesFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magedelight\Authorization\Acl\RootResource $rootResource
    ) {
        parent::__construct($context, $resultPageFactory, $design, $coreRegistry, $roleFactory, $userFactory, $rulesFactory, $filterManager, $vendorHelper);
        $this->rootResource = $rootResource;
    }
    
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $rid = $this->getRequest()->getParam('role_id', false);
        $resource = $this->getRequest()->getParam('resource', false);
        $roleUsers = $this->getRequest()->getParam('in_role_user', null);
        parse_str($roleUsers, $roleUsers);
        $roleUsers = array_keys($roleUsers);

        $isAll = $this->getRequest()->getParam('all');
        
        if ($isAll) {
            $resource = [$this->rootResource->getId()];
        }

        $role = $this->_initRole('role_id');
        if (!$role->getId() && $rid) {
            $this->messageManager->addError(__('This role no longer exists.'));
            return $resultRedirect->setPath('*/*/role_index');
        }

        try {
            $roleName = $this->filterManager->removeTags($this->getRequest()->getParam('rolename', false));
            $role->setName($roleName)
                ->setVendorId($this->_auth->getUser()->getVendorId());
            $this->_eventManager->dispatch(
                'vendor_permissions_role_prepare_save',
                ['object' => $role, 'request' => $this->getRequest()]
            );
            $role->save();
            $this->rulesFactory->create()->setRoleId($role->getId())
                ->setResources($resource)
                ->saveRel();

            foreach ($roleUsers as $nRuid) {
                $this->_addUserToRole($nRuid, $role->getId());
            }
            $this->messageManager->addSuccess(__('You saved the role.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while saving this role.'.$e->getMessage()));
        }

        return $resultRedirect->setPath('*/*/role_index');
    }

    /**
     * Assign user to role
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    protected function _addUserToRole($userId, $roleId)
    {
        $user = $this->userFactory->create()->load($userId);
        $user->setRoleId($roleId);

        if ($user->roleUserExists() === true) {
            return false;
        } else {
            $user->save();
            return true;
        }
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::main');
    }
}
