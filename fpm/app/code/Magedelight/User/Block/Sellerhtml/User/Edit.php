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
namespace Magedelight\User\Block\Sellerhtml\User;

class Edit extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magedelight\Authorization\Model\ResourceModel\Role\CollectionFactory
     */
    protected $userRolesFactory;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->userRolesFactory = $userRolesFactory;
        $this->coreRegistry = $coreRegistry;
        $this->authSession = $authSession;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }
    
    /**
     * @return $this
     */
    public function getUserRoleCollection()
    {
        $collection = $this->userRolesFactory->create();
        $collection->setRolesFilter();
        return $collection;
    }
    
    public function getSubmitUrl($isProfileEdit = false)
    {
        return ($isProfileEdit)?$this->getUrl('rbuser/account/save') : $this->getUrl('rbuser/user/save');
    }
    
    public function getUser()
    {
        if ($this->coreRegistry->registry('permissions_user')->getId()) {
            return $this->coreRegistry->registry('permissions_user');
        } else {
            $obj = new \Magento\Framework\DataObject();
            $obj->setData($this->authSession->getUser()->getData());
            return $obj;
        }
    }

    /**
     * @return int|null
     */
    public function getParentVendorId()
    {
        $user = $this->getUser();
        if ($this->coreRegistry->registry('permissions_user')->getId()) {
            return $user->getParentVendorId();
        } else {
            if ($user->getParentVendorId()) {
                return $user->getParentVendorId();
            } else {
                return $user->getVendorId();
            }
        }
        return null;
    }
}
