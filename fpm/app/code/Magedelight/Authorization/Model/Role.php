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
namespace Magedelight\Authorization\Model;

/**
 * Description of Role
 *
 * @author Rocket Bazaar Core Team
 */
class Role extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'vendor_authorization_roles';

    /**
     * @var \Magedelight\Authorization\Acl\RootResource
     */
    protected $_vendorRootResource;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magedelight\Authorization\Model\ResourceModel\Role $resource
     * @param \Magedelight\Authorization\Model\ResourceModel\Role\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Authorization\Model\ResourceModel\Role $resource,
        \Magedelight\Authorization\Model\ResourceModel\Role\Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function __sleep()
    {
        $properties = parent::__sleep();
        return array_diff($properties, ['_resource', '_resourceCollection']);
    }

    /**
     * {@inheritdoc}
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resource = $objectManager->get(\Magedelight\Authorization\Model\ResourceModel\Role::class);
        $this->_resourceCollection = $objectManager->get(
            \Magedelight\Authorization\Model\ResourceModel\Role\Collection::class
        );
    }

    protected function _construct()
    {
        $this->_init(\Magedelight\Authorization\Model\ResourceModel\Role::class);
    }

    /**
     * Update object into database
     *
     * @return $this
     */
    public function update()
    {
        $this->getResource()->update($this);
        return $this;
    }

    /**
     * Get Default Role ID for main vendor
     * @return int Role ID
     */
    public function getDefaultRoleId()
    {
        return $this->getResource()->getDefaultRoleId($this);
    }
}
