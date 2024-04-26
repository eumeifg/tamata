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
namespace Magedelight\User\Block\Sellerhtml\Role;

/**
 * Description of Grid
 *
 * @author Rocket Bazaar Core Team
 */
class Grid extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magedelight\Authorization\Model\RoleFactory
     */
    protected $roleFactoy;

    const LIMIT = 10;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Authorization\Model\RoleFactory $roleFactoy
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     */
    function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Authorization\Model\RoleFactory $roleFactoy,
        \Magedelight\Backend\Model\Auth\Session $authSession
    ) {
        $this->roleFactoy = $roleFactoy;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setCollection($this->getVendorRoleCollection());
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!empty($this->getCollection())) {

            /** @var \Magento\Theme\Block\Html\Pager */
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'vendor.sizechart.list.pager'
            );
            $pager->setTemplate('Magedelight_Theme::html/pager.phtml');
            $pager->setLimit(self::LIMIT)
                ->setCollection($this->getCollection());
            $this->setChild('pager', $pager);

            $this->getCollection()->load();
            return $this;
        }
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getVendorRoleCollection()
    {
        $collection = $this->roleFactoy->create()->getCollection();
        $collection->addFieldToFilter('vendor_id', $this->authSession->getUser()->getVendorId());
        if ($q = $this->getRequest()->getParam('q', false)) {
            $str = preg_replace('/[^A-Za-z0-9\. -]/', '', $q);
            $collection->getSelect()->where("role_name like '%$str%'");
        }
        return $collection;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     *
     * @return  string
     */
    public function getEditUrl($id = null)
    {
        if ($id) {
            return $this->getUrl('rbuser/user/role_editrole', ['role_id' => $id,'tab' => $this->getRequest()->getParam('tab')]);
        }
    }

    /**
     *
     * @return  string
     */
    public function getDeleteUrl($id = null)
    {
        if ($id) {
            return $this->getUrl('rbuser/user/role_delete/', ['role_id' => $id]);
        }
    }

    /**
     *
     * @return  string
     */
    public function getAddNewUrl()
    {
        $tab = $this->getRequest()->getParam('tab');
        return $this->getUrl('rbuser/user/role_editrole/tab/'.$tab);
    }
}
