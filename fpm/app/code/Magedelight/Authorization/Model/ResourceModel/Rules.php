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
 * Description of Rules
 *
 * @author Rocket Bazaar Core Team
 */
class Rules extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const VENDOR_RESOURCE = 'Magedelight_Vendor::main';

    /**
     * @var \Magento\Framework\Acl\AclResource\ProviderInterface
     */
    protected $_aclResourceProvider;

    /**
     * Root ACL resource
     *
     * @var \Magedelight\Authorization\Acl\RootResource
     */
    protected $_rootResource;

    /**
     * Acl object cache
     *
     * @var \Magento\Framework\Acl\Data\CacheInterface
     */
    protected $_aclCache;

    /**
     * @var \Magento\Framework\Acl\Builder
     */
    protected $_aclBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Acl\Builder $aclBuilder
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magedelight\Authorization\Acl\RootResource $rootResource
     * @param \Magento\Framework\Acl\Data\CacheInterface $aclCache
     * @param \Magento\Framework\Acl\AclResource\ProviderInterface $aclResourceProvider
     * @param type $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Acl\Builder $aclBuilder,
        \Psr\Log\LoggerInterface $logger,
        \Magedelight\Authorization\Acl\RootResource $rootResource,
        \Magento\Framework\Acl\Data\CacheInterface $aclCache,
        \Magento\Framework\Acl\AclResource\ProviderInterface $aclResourceProvider
    ) {
        $this->_aclBuilder = $aclBuilder;
        $this->_rootResource = $rootResource;
        $this->_aclCache = $aclCache;
        $this->_logger = $logger;
        $this->_aclResourceProvider = $aclResourceProvider;
        parent::__construct($context);
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('md_vendor_authorization_rule', 'rule_id');
    }

    /**
     * Save ACL resources
     *
     * @param \Magedelight\Authorization\Model\Rules $rule
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveRel(\Magedelight\Authorization\Model\Rules $rule)
    {
        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            $roleId = $rule->getRoleId();

            $condition = ['role_id = ?' => (int)$roleId];

            $connection->delete($this->getMainTable(), $condition);

            $postedResources = $rule->getResources();

            if ($postedResources) {
                $row = [
                    'resource_id' => $this->_rootResource->getId(),
                    'privileges' => '',
                    'role_id' => $roleId,
                    'permission' => 'allow',
                ];

                /** Give basic vendor permissions to any vendor */
                if ($postedResources === [$this->_rootResource->getId()]) {
                    $insertData = $this->_prepareDataForTable(
                        new \Magento\Framework\DataObject($row),
                        $this->getMainTable()
                    );

                    $connection->insert($this->getMainTable(), $insertData);
                } else {
                    $acl = $this->_aclBuilder->getAcl();
                    foreach ($acl->getResources() as $resourceId) {
                        $row['permission'] = in_array($resourceId, $postedResources) ? 'allow' : 'deny';
                        $row['resource_id'] = $resourceId;

                        $insertData = $this->_prepareDataForTable(
                            new \Magento\Framework\DataObject($row),
                            $this->getMainTable()
                        );

                        $connection->insert($this->getMainTable(), $insertData);
                    }
                }
            }

            $connection->commit();
            $this->_aclCache->clean();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->_logger->critical($e);
        }
    }

    /**
     * Collect ACL resources from all adon-ons and base modules
     * @return Array
     */
    public function getVendorAclResources()
    {
        $resources = $this->_aclResourceProvider->getAclResources();
        return isset($resources[2]['children']) ? $resources[2]['children'] : [];
    }
}
