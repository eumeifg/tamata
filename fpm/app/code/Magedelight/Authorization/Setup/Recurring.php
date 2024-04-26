<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Authorization\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /* Added here to resolve the seller login issue when module installed but vendor created after disabling the
         User * module, so inconsistent data gets created which throws error when enable the User module.*/
        $this->addUserDefaultLinks($installer);
        /* -----------------------------------------------*/
        $installer->endSetup();
    }

    /**
     *
     * @param $installer
     * @return void
     */
    protected function addUserDefaultLinks($installer)
    {
        $data = [
            'sort_order' => 0,
            'role_name' => 'Main Role',
            'vendor_id' => 0
        ];
        $isExistTable = $installer->getConnection()->isTableExists('md_vendor_authorization_role');
        if ($isExistTable) {
            $select = $installer->getConnection()->select()->from(
                'md_vendor_authorization_role',
                'role_id'
            )->where('role_name LIKE "Main Role"')->order('role_id');
            $isExistRecords = $installer->getConnection()->fetchAll($select);
            if (!(count($isExistRecords) > 0)) {
                $installer->getConnection()->insertOnDuplicate('md_vendor_authorization_role', $data);
            }
        }
        $selectRoleId = $installer->getConnection()->select()->from(
            'md_vendor_authorization_role',
            'role_id'
        )->where('role_name = "Main Role"')->order('role_id');
        $isRoleId = $installer->getConnection()->fetchAll($selectRoleId);
        $isExistTableUserLink = $installer->getConnection()->isTableExists('md_vendor_user_link');
        if ($isExistTableUserLink) {
            $selectUserLink = $installer->getConnection()->select()->from(
                'md_vendor_user_link'
            )->where(1)->order('row_id');
            $isExistRecordsUserLink = $installer->getConnection()->fetchAll($selectUserLink);
            if (count($isExistRecordsUserLink) > 0) {
                $selectVendor = $installer->getConnection()->select()->from(
                    'md_vendor',
                    'vendor_id'
                )->where('parent_vendor_id IS NULL')->order('vendor_id');
                $isExistRecordsVendors = $installer->getConnection()->fetchAll($selectVendor);
                foreach ($isExistRecordsVendors as $value) {
                    foreach ($value as $val) {
                        $vendorIds[] = $val;
                    }
                }
                foreach ($isExistRecordsUserLink as $single) {
                    if ($single['parent_id'] == 0) {
                        $vendorIdsExists[] = $single['vendor_id'];
                    }
                }
                $dataDiffArray = [];
                $diff = array_diff($vendorIds, $vendorIdsExists);
                if (!empty($diff)) {
                    foreach ($diff as $val) {
                        $dataDiffArray[] = [
                            'vendor_id' => $val,
                            'parent_id' => 0,
                            'role_id' => $isRoleId[0]['role_id']
                        ];
                    }
                    foreach ($dataDiffArray as $row) {
                        $installer->getConnection()->insertOnDuplicate('md_vendor_user_link', $row);
                    }
                }
            } else {
                $dataArray = [];
                $selectVendor = $installer->getConnection()->select()->from(
                    'md_vendor',
                    'vendor_id'
                )->where('parent_vendor_id IS NULL')->order('vendor_id');
                $isExistRecordsVendor = $installer->getConnection()->fetchAll($selectVendor);
                foreach ($isExistRecordsVendor as $value) {
                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $dataArray[] = [
                                'vendor_id' => $val,
                                'parent_id' => 0,
                                'role_id' => $isRoleId[0]['role_id']
                            ];
                        }
                    }
                }
                foreach ($dataArray as $row) {
                    $installer->getConnection()->insertOnDuplicate('md_vendor_user_link', $row);
                }
            }
        }
        $isExistTableAuthRule = $installer->getConnection()->isTableExists('md_vendor_authorization_rule');
        if ($isExistTableAuthRule) {
            $selectUserLink = $installer->getConnection()->select()->from(
                'md_vendor_authorization_rule'
            )->where(1)->order('rule_id');
            $isExistRecordsUserLink = $installer->getConnection()->fetchAll($selectUserLink);
            if (count($isExistRecordsUserLink) <= 0) {
                $authLinkData = [
                    'role_id' => $isRoleId[0]['role_id'],
                    'resource_id' => 'Magedelight_Vendor::main',
                    'privileges' => null,
                    'permission' => 'allow'
                ];
                $installer->getConnection()->insertOnDuplicate('md_vendor_authorization_rule', $authLinkData);
            }
        }
    }
}
