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

class Grid extends \Magento\Framework\View\Element\Template
{

    const LIMIT = 10;
    
    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magedelight\User\Model\UserFactory
     */
    protected $userFactory;

    /**
     * @var \Magedelight\User\Model\User
     */
    protected $user;
    
    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\User\Model\UserFactory $userFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\User\Model\User $user
     */
    function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\User\Model\UserFactory $userFactory,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magedelight\User\Model\User $user
    ) {
        $this->userFactory = $userFactory;
        $this->vendorFactory = $vendorFactory;
        $this->authSession = $authSession;
        $this->user = $user;
        parent::__construct($context);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setCollection($this->getVendorUserCollection());
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!empty($this->getCollection())) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'vendor.user.list.pager'
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
    public function getVendorUserCollection()
    {
        $vendorId = (int)$this->authSession->getUser()->getVendorId();
        $userIds = $this->vendorFactory->create()->getVendorAllChildUsers($vendorId);

        if (count($userIds)) {
            $collection = $this->vendorFactory->create()->getCollection();
            $collection->join(['rbvwd' => 'md_vendor_website_data'], 'rbvwd.vendor_id = main_table.vendor_id and rbvwd.website_id = main_table.website_id ', ['name', 'status']);
            $collection->getSelect()->joinLeft(['mvul' => 'md_vendor_user_link'], 'main_table.vendor_id = mvul.vendor_id', ['role_id']);
            $collection->getSelect()->joinLeft(['rbv_role' => 'md_vendor_authorization_role'], 'rbv_role.role_id = mvul.role_id', ['role_name']);
            $collection->addFieldToFilter('main_table.parent_vendor_id', ['eq'=> $vendorId]);
            
            $q = $this->getRequest()->getParam('q');
            if (!empty($q)) {
                $collection->getSelect()->where('rbvwd.name like "%' . $q . '%" OR rbv_role.role_name like "%' . $q . '%"');
            }

            if (!empty($this->getRequest()->getParam('name'))) {
                $collection->addFieldToFilter('rbvwd.name', ['like'=> "%".$this->getRequest()->getParam('name')."%"]);
            }

            if (!empty($this->getRequest()->getParam('email'))) {
                $collection->addFieldToFilter('main_table.email', ['like'=> "%".$this->getRequest()->getParam('email')."%"]);
            }

            if ($this->getRequest()->getParam('status') == '2' || $this->getRequest()->getParam('status') == '1') {
                $collection->addFieldToFilter('rbvwd.status', ['eq'=> $this->getRequest()->getParam('status')]);
            }
            $collection->addFieldToFilter('main_table.vendor_id', ['neq'=> $this->authSession->getUser()->getId()]);

            return $collection;
        }
        return null;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return  string
     */
    public function getEditUrl($id = null)
    {
        if ($id) {
            return $this->getUrl('rbuser/user/edit', ['user_id' => $id]);
        }
    }

    /**
     * @return  string
     */
    public function getDeleteUrl($id = null)
    {
        if ($id) {
            return $this->getUrl('rbuser/user/delete/', ['user_id' => $id]);
        }
    }

    /**
     * @return  string
     */
    public function getAddNewUrl()
    {
        $tab = $this->getRequest()->getParam('tab');
        return $this->getUrl('rbuser/user/new/tab/' . $tab);
    }

    public function toOptionArray()
    {
        $select = '<select name="status" id="status" title="" class="select filter" placeholder="">';
        $select .= '<option value="">' . __('-- Select Status --') . '</option>';
        foreach ($this->user->getAvailableStatuses() as $code => $label) {
            if ($code) {
                $selected = ($code == $this->getRequest()->getParam('status')) ? 'selected = selected' : '';
                $select .= '<option ' . $selected . ' value="' . $code . '">' . $label . '</option>';
            }
        }
        $select .= '</select>';
        return $select;
    }
}
