<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\Authorization\Setup\Patch\Data;

use Magedelight\Authorization\Acl\RootResource;
use Magedelight\Authorization\Model\RoleFactory;
use Magedelight\Authorization\Model\RulesFactory;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class InstallDataPatch implements
    DataPatchInterface,
    PatchRevertableInterface,
    PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    private $appState;

    private $_vendorRootResource;

    private $_roleFactory;

    private $_rulesFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        State $appState,
        RootResource $vendorRootResource,
        RoleFactory $roleFactory,
        RulesFactory $rulesFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->appState = $appState;
        $this->_vendorRootResource = $vendorRootResource;
        $this->_roleFactory = $roleFactory;
        $this->_rulesFactory = $rulesFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $this->appState->emulateAreaCode('frontend', [$this, 'createMainRole'], [$this->moduleDataSetup]);
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    public function revert()
    {
    }

    public static function getVersion()
    {
        return '1.0.0';
    }

    public function createMainRole($installer)
    {
        $this->_createMainRole($installer);
    }

    protected function _createMainRole($installer)
    {
        $select = $installer->getConnection()->select()->from(
            'md_vendor_authorization_role',
            ['role_id']
        )->where('vendor_id = 0');
        $isExistDefaultRole = $installer->getConnection()->fetchOne($select);
        if ((!$isExistDefaultRole)) {
            $role = $this->_initRole();
            $role->setName('Main Role');
            $role->setVendorId(0);
            $role->save();
            $resource = [$this->_vendorRootResource->getId()];
            $this->_rulesFactory->create()->setRoleId($role->getId())->setResources($resource)->saveRel();
            $this->addUserDefaultLinks($installer, $role->getId());
        }
    }

    /**
     * Initialize role model by passed parameter in request
     *
     * @param string $requestVariable
     * @return \Magedelight\Authorization\Model\Role
     */
    protected function _initRole()
    {
        $role = $this->_roleFactory->create();
        return $role;
    }

    /**
     *
     * @return void
     */
    public function addUserDefaultLinks($installer, $roleId = '')
    {
        if ($roleId) {
            $query = new \Zend_Db_Expr('INSERT INTO md_vendor_user_link (vendor_id, role_id) SELECT md_vendor.vendor_id, ' . $roleId . ' as role_id from md_vendor ON DUPLICATE KEY UPDATE vendor_id = VALUES(vendor_id), role_id = VALUES(role_id)');
            $installer->getConnection()->query($query);
        }
    }
}
