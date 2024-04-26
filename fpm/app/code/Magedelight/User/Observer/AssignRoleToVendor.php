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
namespace Magedelight\User\Observer;

class AssignRoleToVendor implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magedelight\Authorization\Model\RoleFactory
     */
    protected $roleFactory;

    protected $resourceConnection;
    
    protected $resources = null;
    
    /**
     * @param \Magedelight\Authorization\Model\RoleFactory $roleFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magedelight\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->roleFactory = $roleFactory;
        $this->resourceConnection = $resourceConnection;
    }
    
    /**
     * Insert link entry
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $vendor = $observer->getEvent()->getVendor();
            $defaultRoleId = $this->roleFactory->create()->getDefaultRoleId();

            $this->resources = $this->resourceConnection;

            $connection = $this->resources->getConnection();

            $table = $this->resources->getTableName('md_vendor_user_link');
            $where = ['vendor_id = ?' => (int) $vendor->getId()];
            $connection->delete($table, $where);

            $data = ['vendor_id' => (int) $vendor->getId(), 'parent_id' => (int) 0, 'role_id' => (int) $defaultRoleId ];
            $connection->insert($table, $data);
        } catch (\Exception $e) {
            throw new \Exception(__('Failed to save vendor role.'));
        }
    }
}
