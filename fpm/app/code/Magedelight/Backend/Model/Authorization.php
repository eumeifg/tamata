<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Model;

use Magedelight\Vendor\Model\Source\Status;

/**
 * Description of Authorization
 *
 * @author Rocket Bazaar Core Team
 */
class Authorization implements \Magedelight\Backend\Api\Data\VendorAuthorizationInterface
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magedelight\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * ACL policy
     *
     * @var \Magento\Framework\Authorization\PolicyInterface
     */
    protected $_aclPolicy;

    /**
     * ACL role locator
     *
     * @var \Magento\Framework\Authorization\RoleLocatorInterface
     */
    protected $_aclRoleLocator;
    
    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     *
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     * @param \Magento\Framework\Authorization\RoleLocatorInterface $roleLocator
     * @param \Magedelight\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Framework\Authorization\PolicyInterface $aclPolicy,
        \Magento\Framework\Authorization\RoleLocatorInterface $roleLocator,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magedelight\Backend\Model\Auth\Session $authSession
    ) {
        $this->_aclPolicy = $aclPolicy;
        $this->_aclRoleLocator = $roleLocator;
        $this->_moduleManager = $moduleManager;
        $this->authSession = $authSession;
    }

    /**
     * Check current vendor user permission on resource
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     */
    public function isAllowed($resource, $privilege = null)
    {
        $allowedResources = true;
        if ($this->authSession->isLoggedIn()) {
            $this->authSession->getUser()->getStatus();
            if (!in_array($this->authSession->getUser()->getStatus(), [Status::VENDOR_STATUS_ACTIVE, Status::VENDOR_STATUS_VACATION_MODE])) {
                $allowedResources = ['Magedelight_Vendor::account'];
            } else {
                if ($this->_isOutputEnabled('Magedelight_User')) {
                    $this->_userFactory = \Magento\Framework\App\ObjectManager::getInstance()->get('Magedelight\User\Model\UserFactory');
                    $allowedResources = $this->_userFactory->create()->getAllowedResourcesByRole();
                }
            }
        }
        
        if (is_array($allowedResources)) {
            if (in_array($resource, $allowedResources) || $resource == "Magedelight_Vendor::account_dashboard") {
                return true;
            }
        } elseif ($allowedResources) {
            return true;
        }
        return false;
    }
    
    /**
     * Whether a module is enabled in the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     */
    protected function _isModuleEnabled($moduleName)
    {
        return $this->_moduleManager->isEnabled($moduleName);
    }
    
    /**
     * Whether a module output is permitted by the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     */
    protected function _isOutputEnabled($moduleName)
    {
        return $this->_moduleManager->isOutputEnabled($moduleName);
    }
}
