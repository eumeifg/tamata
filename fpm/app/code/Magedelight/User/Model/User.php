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
namespace Magedelight\User\Model;

use Magedelight\User\Api\Data\UserInterface;
use Magento\Framework\Acl\Builder as AclBuilder;

/**
 * vendor user model
 *
 * @method \Magedelight\User\Model\ResourceModel\User _getResource()
 * @method \Magedelight\User\Model\ResourceModel\User getResource()
 * @method string getLogdate()
 * @method \Magento\User\Model\User setLogdate(string $value)
 * @method int getLognum()
 * @method \Magedelight\User\Model\User setLognum(int $value)
 * @method int getReloadAclFlag()
 * @method \Magedelight\User\Model\User setReloadAclFlag(int $value)
 * @method string getExtra()
 * @method \Magedelight\User\Model\User setExtra(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class User extends \Magento\Framework\Model\AbstractModel implements UserInterface
{
    const TYPE_ALLOW = 'allow';
    const TYPE_DENY = 'deny';

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Authorization level of a basic (Main) vendor session
     */
    const VENDOR_RESOURCE = 'Magedelight_Vendor::main';

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'vendor_user';

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var AclBuilder
     */
    protected $aclBuilder;

    /**
     * @var \Magedelight\Authorization\Model\ResourceModel\Rules\CollectionFactory
     */
    protected $ruleCollectionFactory;
    
    /**
     * Seller role
     *
     * @var \Magedelight\Authorization\Model\Role
     */
    protected $role;

    /**
     * Available resources flag
     *
     * @var bool
     */
    protected $hasResources = true;

    /**
     * Role model factory
     *
     * @var \Magedelight\Authorization\Model\RoleFactory
     */
    protected $roleFactory;

    /**
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magedelight\Authorization\Model\RoleFactory $roleFactory
     * @param \Magedelight\Authorization\Model\ResourceModel\Rules\CollectionFactory $ruleCollectionFactory
     * @param AclBuilder $aclBuilder
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Authorization\Model\RoleFactory $roleFactory,
        \Magedelight\Authorization\Model\ResourceModel\Rules\CollectionFactory $ruleCollectionFactory,
        AclBuilder $aclBuilder,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->roleFactory = $roleFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->aclBuilder = $aclBuilder;
        $this->authSession = $authSession;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize user model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magedelight\User\Model\ResourceModel\User');
    }
    
    /**
     * Retrieve user roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->_getResource()->getRoles($this);
    }

    /**
     * Get seller role model
     *
     * @return \Magento\Authorization\Model\Role
     */
    public function getRole()
    {
        if (null === $this->role) {
            $this->role = $this->roleFactory->create();
            $roles = $this->getRoles();
            if ($roles && isset($roles[0]) && $roles[0]) {
                $this->role->load($roles[0]);
            }
        }
        return $this->role;
    }

    /**
     * Unassign user from his current role
     *
     * @return $this
     */
    public function deleteFromRole()
    {
        $this->_getResource()->deleteFromRole($this);
        return $this;
    }

    /**
     * Check if such combination role/user exists
     *
     * @return bool
     */
    public function roleUserExists()
    {
        $result = $this->_getResource()->roleUserExists($this);
        return is_array($result) && count($result) > 0 ? true : false;
    }


    /**
     * Get user ACL role
     *
     * @return string
     */
    public function getAclRole()
    {
        return $this->getRole()->getId();
    }

    /**
     * Check if user is assigned to any role
     *
     * @param int|\Magento\User\Model\User $user
     * @return null|array
     */
    public function hasAssigned2Role($user)
    {
        return $this->getResource()->hasAssigned2Role($user);
    }


    /**
     * Check if user has available resources
     *
     * @return bool
     */
    public function hasAvailableResources()
    {
        return $this->hasResources;
    }

    /**
     * Set user has available resources
     *
     * @param bool $hasResources
     * @return $this
     */
    public function setHasAvailableResources($hasResources)
    {
        $this->hasResources = $hasResources;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->_getData('vendor_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData('vendor_id', $id);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData('name', $name);
    }


    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->_getData('email');
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData('email', $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->_getData('status');
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($status)
    {
        return $this->setData('status', $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getInterfaceLocale()
    {
        return $this->_getData('interface_locale');
    }

    /**
     * {@inheritdoc}
     */
    public function setInterfaceLocale($interfaceLocale)
    {
        return $this->setData('interface_locale', $interfaceLocale);
    }

    
    /**
     * Return users for role
     *
     * @return array
     */
    public function getVendorChildUsers($parentVendorId)
    {
        return $this->getResource()->getVendorChildUsers($parentVendorId);
    }
    /**
     * get Logged vendor role id.
     * @return mixed
     */
    public function getLoggedVendorRoleId($userId = null)
    {
        if (is_null($userId)) {
            $user = $this->load($this->authSession->getUser()->getId());
        } else {
            $user = $this->load($userId);
        }
        
        if ($user->getParentId()) {
            return $user;
        }
        return $user;
    }
    
    /**
     * get allowed resources (Menu) by user role
     * @param type $roleId
     * @return Array
     */
    public function getAllowedResourcesByRole($roleId = null)
    {
        if (is_null($roleId)) {
            $roleId = $this->getLoggedVendorRoleId()->getRoleId();
        }

        if (!is_null($roleId)) {
            $allowedResources = [];
            $rulesCollection = $this->ruleCollectionFactory->create();
            $rulesCollection->getByRoles($roleId)->load();
            $acl = $this->aclBuilder->getAcl();
            /** @var \Magedelight\Authorization\Model\Rules $ruleItem */
            foreach ($rulesCollection->getItems() as $ruleItem) {
                $resourceId = $ruleItem->getResourceId();
                if ($acl->has($resourceId) && $ruleItem->getPermission() == self::TYPE_ALLOW) {
                    $allowedResources[] = $resourceId;
                }
            }
            
            if (in_array(self::VENDOR_RESOURCE, $allowedResources)) {
                return true;
            }
            return $allowedResources;
        }
    }

    public function getUserParentId($userId)
    {
        return $this->getResource()->getUserParentId($userId);
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}
