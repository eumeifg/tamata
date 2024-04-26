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
namespace Magedelight\User\Model\ResourceModel;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\Acl\Role\User as RoleUser;
use Magento\Authorization\Model\UserContextInterface;
use Magedelight\User\Model\User as ModelUser;

class User extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface
     */
    protected $aclCache;

    /**
     * Role model
     *
     * @var \Magedelight\Authorization\Model\RoleFactory
     */
    protected $roleFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Acl\Data\CacheInterface $aclCache
     * @param \Magedelight\Authorization\Model\RoleFactory $roleFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Acl\Data\CacheInterface $aclCache,
        \Magedelight\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $connectionName = null
    ) {
        $this->aclCache = $aclCache;
        $this->roleFactory = $roleFactory;
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }
    
    protected function _construct()
    {
        $this->_init('md_vendor_user_link', 'vendor_id');
    }
    
    /**
     * Initialize unique fields
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [
            ['field' => 'email', 'title' => __('Email')],
            ['field' => 'username', 'title' => __('User Name')],
        ];
        return $this;
    }

    /**
     * Authenticate user by $username and $password
     *
     * @param ModelUser $user
     * @return $this
     */
    public function recordLogin(ModelUser $user)
    {
        $connection = $this->getConnection();

        $data = [
            'logdate' => (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'lognum' => $user->getLognum() + 1,
        ];

        $condition = ['user_id = ?' => (int)$user->getUserId()];

        $connection->update($this->getMainTable(), $data, $condition);

        return $this;
    }

    /**
     * Load data by specified username
     *
     * @param string $username
     * @return array
     */
    public function loadByUsername($username)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable())->where('username=:username');

        $binds = ['username' => $username];

        return $connection->fetchRow($select, $binds);
    }

    /**
     * Check if user is assigned to any role
     *
     * @param int|ModelUser $user
     * @return null|array
     */
    public function hasAssigned2Role($user)
    {
        if (is_numeric($user)) {
            $userId = $user;
        } elseif ($user instanceof \Magento\Framework\Model\AbstractModel) {
            $userId = $user->getUserId();
        } else {
            return null;
        }

        if ($userId > 0) {
            $connection = $this->getConnection();

            $select = $connection->select();
            $select->from($this->getTable('md_vendor_authorization_role'))
                ->where('parent_id > :parent_id')
                ->where('user_id = :user_id')
                ->where('user_type = :user_type');

            $binds = ['parent_id' => 0, 'user_id' => $userId,
                      'user_type' => UserContextInterface::USER_TYPE_ADMIN
            ];

            return $connection->fetchAll($select, $binds);
        } else {
            return null;
        }
    }

    /**
     * Unserialize user extra data after user save
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $user)
    {
        $user->setExtra(unserialize($user->getExtra()));
        if ($user->hasRoleId()) {
            $this->_clearUserRoles($user);
        }
        return $this;
    }

    /**
     * Clear all user-specific roles of provided user
     *
     * @param ModelUser $user
     * @return void
     */
    public function _clearUserRoles(ModelUser $user)
    {
        $conditions = ['user_id = ?' => (int)$user->getId(), 'user_type = ?' => UserContextInterface::USER_TYPE_ADMIN];
        $this->getConnection()->delete($this->getTable('md_vendor_authorization_role'), $conditions);
    }

    /**
     * Create role for provided user of provided type
     *
     * @param int $parentId
     * @param ModelUser $user
     * @return void
     */
    protected function _createUserRole($parentId, ModelUser $user)
    {
        if ($parentId > 0) {
            /** @var \Magedelight\User\Model\Role $parentRole */
            $parentRole = $this->roleFactory->create()->load($parentId);
        } else {
            $role = new \Magento\Framework\DataObject();
            $role->setTreeLevel(0);
        }

        if ($parentRole->getId()) {
            $data = new \Magento\Framework\DataObject(
                [
                    'parent_id' => $parentRole->getId(),
                    'tree_level' => $parentRole->getTreeLevel() + 1,
                    'sort_order' => 0,
                    'role_type' => RoleUser::ROLE_TYPE,
                    'user_id' => $user->getId(),
                    'user_type' => UserContextInterface::USER_TYPE_ADMIN,
                    'role_name' => $user->getFirstname(),
                ]
            );

            $insertData = $this->_prepareDataForTable($data, $this->getTable('md_vendor_authorization_role'));
            $this->getConnection()->insert($this->getTable('md_vendor_authorization_role'), $insertData);
            $this->aclCache->clean();
        }
    }

    /**
     * Unserialize user extra data after user load
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $user)
    {
      
        if (is_string($user->getExtra())) {
            $user->setExtra(unserialize($user->getExtra()));
        }
        return parent::_afterLoad($user);
    }

    /**
     * Delete user role record with user
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magento\Framework\Model\AbstractModel $user)
    {
        $this->_beforeDelete($user);
        $connection = $this->getConnection();

        $uid = $user->getId();
        $connection->beginTransaction();
        try {
            $connection->delete($this->getMainTable(), ['user_id = ?' => $uid]);
            $connection->delete(
                $this->getTable('md_vendor_authorization_role'),
                ['user_id = ?' => $uid, 'user_type = ?' => UserContextInterface::USER_TYPE_ADMIN]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            return false;
        }
        $connection->commit();
        $this->_afterDelete($user);
        return true;
    }

    /**
     * Get user roles
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return array
     */
    public function getRoles(\Magento\Framework\Model\AbstractModel $user)
    {
       
        if (!$user->getId()) {
            return [];
        }

        $table = $this->getTable('md_vendor_authorization_role');
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $table,
            []
        )->joinLeft(
            ['ar' => $table],
            "(ar.role_id = {$table}.parent_id and ar.role_type = '" . RoleGroup::ROLE_TYPE . "')",
            ['role_id']
        )->where(
            "{$table}.user_id = :user_id"
        )->where(
            "{$table}.user_type = :user_type"
        );

        $binds = ['user_id' => (int)$user->getId(),
                  'user_type' => UserContextInterface::USER_TYPE_ADMIN
        ];
        $roles = $connection->fetchCol($select, $binds);

        if ($roles) {
            return $roles;
        }

        return [];
    }

    /**
     * Delete user role
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return $this
     */
    public function deleteFromRole(\Magento\Framework\Model\AbstractModel $user)
    {
        if ($user->getUserId() <= 0) {
            return $this;
        }
        if ($user->getRoleId() <= 0) {
            return $this;
        }

        $dbh = $this->getConnection();

        $condition = [
            'user_id = ?' => (int)$user->getId(),
            'parent_id = ?' => (int)$user->getRoleId(),
            'user_type = ?' => UserContextInterface::USER_TYPE_ADMIN
        ];

        $dbh->delete($this->getTable('md_vendor_authorization_role'), $condition);
        return $this;
    }

    /**
     * Check if role user exists
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return array
     */
    public function roleUserExists(\Magento\Framework\Model\AbstractModel $user)
    {
        if ($user->getUserId() > 0) {
            $roleTable = $this->getTable('md_vendor_authorization_role');

            $dbh = $this->getConnection();

            $binds = [
                'parent_id' => $user->getRoleId(),
                'user_id' => $user->getUserId(),
                'user_type' => UserContextInterface::USER_TYPE_ADMIN
            ];

            $select = $dbh->select()->from($roleTable)
                ->where('parent_id = :parent_id')
                ->where('user_type = :user_type')
                ->where('user_id = :user_id');

            return $dbh->fetchCol($select, $binds);
        } else {
            return [];
        }
    }

    /**
     * Check if user exists
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return array
     */
    public function userExists(\Magento\Framework\Model\AbstractModel $user)
    {
        $connection = $this->getConnection();
        $select = $connection->select();

        $binds = [
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'user_id' => (int)$user->getId(),
        ];

        $select->from(
            $this->getMainTable()
        )->where(
            '(username = :username OR email = :email)'
        )->where(
            'user_id <> :user_id'
        );
        return $connection->fetchRow($select, $binds);
    }

    /**
     * Whether a user's identity is confirmed
     *
     * @param \Magento\Framework\Model\AbstractModel $user
     * @return bool
     */
    public function isUserUnique(\Magento\Framework\Model\AbstractModel $user)
    {
        return !$this->userExists($user);
    }

    /**
     * Save user extra data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $data
     * @return $this
     */
    public function saveExtra($object, $data)
    {
        if ($object->getId()) {
            $this->getConnection()->update(
                $this->getMainTable(),
                ['extra' => $data],
                ['user_id = ?' => (int)$object->getId()]
            );
        }

        return $this;
    }

    /**
     * Retrieve the total user count bypassing any filters applied to collections
     *
     * @return int
     */
    public function countAll()
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), 'COUNT(*)');
        $result = (int)$connection->fetchOne($select);
        return $result;
    }

    /**
     * Add validation rules to be applied before saving an entity
     *
     * @return \Zend_Validate_Interface $validator
     */
    public function getValidationRulesBeforeSave()
    {
        $userIdentity = new \Zend_Validate_Callback([$this, 'isUserUnique']);
       
        $userIdentity->setMessage(
            __('A user with the same user name or email already exists.'),
            \Zend_Validate_Callback::INVALID_VALUE
        );
        
        return $userIdentity;
    }

    /**
     * Unlock specified user record(s)
     *
     * @param int|int[] $userIds
     * @return int number of affected rows
     */
    public function unlock($userIds)
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }
        return $this->getConnection()->update(
            $this->getMainTable(),
            ['failures_num' => 0, 'first_failure' => null, 'lock_expires' => null],
            $this->getIdFieldName() . ' IN (' . $this->getConnection()->quote($userIds) . ')'
        );
    }

    /**
     * Lock specified user record(s)
     *
     * @param int|int[] $userIds
     * @param int $exceptId
     * @param int $lifetime
     * @return int number of affected rows
     */
    public function lock($userIds, $exceptId, $lifetime)
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }
        $exceptId = (int)$exceptId;
        return $this->getConnection()->update(
            $this->getMainTable(),
            ['lock_expires' => $this->dateTime->formatDate(time() + $lifetime)],
            "{$this->getIdFieldName()} IN (" . $this->getConnection()->quote(
                $userIds
            ) . ")\n            AND {$this->getIdFieldName()} <> {$exceptId}"
        );
    }

    /**
     * Increment failures count along with updating lock expire and first failure dates
     *
     * @param ModelUser $user
     * @param int|bool $setLockExpires
     * @param int|bool $setFirstFailure
     * @return void
     */
    public function updateFailure($user, $setLockExpires = false, $setFirstFailure = false)
    {
        $update = ['failures_num' => new \Zend_Db_Expr('failures_num + 1')];
        if (false !== $setFirstFailure) {
            $update['first_failure'] = $this->dateTime->formatDate($setFirstFailure);
            $update['failures_num'] = 1;
        }
        if (false !== $setLockExpires) {
            $update['lock_expires'] = $this->dateTime->formatDate($setLockExpires);
        }
        $this->getConnection()->update(
            $this->getMainTable(),
            $update,
            $this->getConnection()->quoteInto("{$this->getIdFieldName()} = ?", $user->getId())
        );
    }

    /**
     * Purge and get remaining old password hashes
     *
     * @param ModelUser $user
     * @param int $retainLimit
     * @return array
     */
    public function getOldPasswords($user, $retainLimit = 4)
    {
        $userId = (int)$user->getId();
        $table = $this->getTable('md_vendor_user_password');

        // purge expired passwords, except those which should be retained
        $retainPasswordIds = $this->getConnection()->fetchCol(
            $this->getConnection()
                ->select()
                ->from($table, 'password_id')
                ->where('user_id = :user_id')
                ->order('expires ' . \Magento\Framework\DB\Select::SQL_DESC)
                ->order('password_id ' . \Magento\Framework\DB\Select::SQL_DESC)
                ->limit($retainLimit),
            [':user_id' => $userId]
        );
        $where = ['user_id = ?' => $userId, 'expires <= ?' => time()];
        if ($retainPasswordIds) {
            $where['password_id NOT IN (?)'] = $retainPasswordIds;
        }
        $this->getConnection()->delete($table, $where);

        // get all remaining passwords
        return $this->getConnection()->fetchCol(
            $this->getConnection()
                ->select()
                ->from($table, 'password_hash')
                ->where('user_id = :user_id'),
            [':user_id' => $userId]
        );
    }

    /**
     * Remember a password hash for further usage
     *
     * @param ModelUser $user
     * @param string $passwordHash
     * @param int $lifetime
     * @return void
     */
    public function trackPassword($user, $passwordHash, $lifetime)
    {
        $now = time();
        $this->getConnection()->insert(
            $this->getTable('md_vendor_user_password'),
            [
                'user_id' => $user->getId(),
                'password_hash' => $passwordHash,
                'expires' => $now + $lifetime,
                'last_updated' => $now
            ]
        );
    }

    /**
     * Get latest password for specified user id
     * Possible false positive when password was changed several times with different lifetime configuration
     *
     * @param int $userId
     * @return array
     */
    public function getLatestPassword($userId)
    {
        return $this->getConnection()->fetchRow(
            $this->getConnection()
                ->select()
                ->from($this->getTable('md_vendor_user_password'))
                ->where('user_id = :user_id')
                ->order('password_id ' . \Magento\Framework\DB\Select::SQL_DESC)
                ->limit(1),
            [':user_id' => $userId]
        );
    }
    
    /**
     * Return users for role
     *
     * @return array
     */
    public function getVendorChildUsers($vendorId)
    {
        return $this->getConnection()->fetchAll(
            $this->getConnection()
                    ->select()
                    ->from($this->getTable('md_vendor_user_link'))
                    ->where('parent_id = :parent_id')
                    ->order('vendor_id ' . \Magento\Framework\DB\Select::SQL_ASC),
            [':parent_id' => $vendorId]
        );
    }

    /**
     * Return user parent vendorId
     *
     * @return array
     */
    public function getUserParentId($userId)
    {
        /*
        return $this->getConnection()->fetchAll(
            $this->getConnection()
                    ->select()
                    ->from($this->getTable('md_vendor_user_link'), 'parent_id')
                    ->where('vendor_id = :vendor_id');
                [':vendor_id' => $userId]
        );
*/
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getTable('md_vendor_user_link'), 'parent_id')
            ->where('vendor_id = :vendor_id');

        $binds = ['vendor_id' => $userId];

        $row = $connection->fetchRow($select, $binds);
        return $row['parent_id'];
    }
}
