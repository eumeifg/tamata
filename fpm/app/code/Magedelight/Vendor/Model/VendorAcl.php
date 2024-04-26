<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model;

class VendorAcl extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Authorization level of a basic (Main) vendor session
     */
    const VENDOR_RESOURCE = 'Magedelight_Vendor::main';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Authorization\Model\ResourceModel\Role $role,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->role = $role;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getLoggedVendorRoleId($userId = null)
    {
        return $this->role->getDefaultRoleId();
    }
    /**
     * Get selected resources
     *
     * @return string
     */
    public function getAllowedResourcesByRole($roleId = null)
    {
        return true;
    }
}
