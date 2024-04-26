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
namespace Magedelight\User\Block\Sellerhtml\User\Edit;

class Roles extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magedelight\Authorization\Model\ResourceModel\Role\CollectionFactory
     */
    protected $userRolesFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->userRolesFactory = $userRolesFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'assigned_user_role') {
            $userRoles = $this->getSelectedRoles();
            if (empty($userRoles)) {
                $userRoles = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('role_id', ['in' => $userRoles]);
            } else {
                if ($userRoles) {
                    $this->getCollection()->addFieldToFilter('role_id', ['nin' => $userRoles]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
    protected function _prepareLayout()
    {
        $collection = $this->userRolesFactory->create();
        $collection->setRolesFilter();
        $this->setCollection($collection);
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $userPermissions = $this->coreRegistry->registry('permissions_user');
        return $this->getUrl('*/*/rolesGrid', ['user_id' => $userPermissions->getUserId()]);
    }

    /**
     * @param bool $json
     * @return array|string
     */
    public function getSelectedRoles()
    {
        if ($this->getRequest()->getParam('user_roles') != "") {
            return $this->getRequest()->getParam('user_roles');
        }
        /* @var $user \Magedelight\Vendor\Model\Vendor*/
        $user = $this->coreRegistry->registry('permissions_user');
        //checking if we have this data and we
        //don't need load it through resource model
        
        if ($user->hasData('roles')) {
            $uRoles = $user->getData('roles');
        } else {
            $uRoles = $user->getRoles();
        }
        return $uRoles;
    }
}
