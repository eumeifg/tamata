<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Authorization
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Authorization\Model\ResourceModel;

/**
 * Description of Role
 *
 * @author Rocket Bazaar Core Team
 */
class Role extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const DEFAULT_ROLE_NAME = 'Main Role';
    /**
     * Rule table
     *
     * @var string
     */
    protected $_ruleTable;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }
    
    protected function _construct()
    {
        $this->_init('md_vendor_authorization_role', 'role_id');
        $this->_ruleTable = $this->getTable('md_vendor_authorization_rule');
    }

    /**
     * Process role before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $role
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $role)
    {
        if ($role->getId() == '') {
            if ($role->getIdFieldName()) {
                $role->unsetData($role->getIdFieldName());
            } else {
                $role->unsetData('id');
            }
        }

        if (!$role->getTreeLevel()) {
            if ($role->getPid() > 0) {
                $select = $this->getConnection()->select()->from(
                    $this->getMainTable(),
                    ['tree_level']
                )->where(
                    "{$this->getIdFieldName()} = :pid"
                );

                $binds = ['pid' => (int)$role->getPid()];

                $treeLevel = $this->getConnection()->fetchOne($select, $binds);
            } else {
                $treeLevel = 0;
            }

            $role->setTreeLevel($treeLevel + 1);
        }

        if ($role->getName()) {
            $role->setRoleName($role->getName());
        }

        return $this;
    }

    /**
     * Process role after deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $role
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $role)
    {
        $connection = $this->getConnection();

        $connection->delete($this->_ruleTable, ['role_id = ?' => (int)$role->getId()]);

        return $this;
    }

    /**
     * Get Default Role ID for main vendor
     * @return type
     */
    public function getDefaultRoleId()
    {
        $connection = $this->getConnection();

        $binds = ['vendor_id' => 0];

        $select = $connection->select()
            ->from($this->getMainTable(), ['role_id'])
            ->where('vendor_id = :vendor_id');

        return $connection->fetchOne($select, $binds);
    }
}
