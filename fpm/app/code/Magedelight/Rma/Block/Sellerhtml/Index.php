<?php
/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */
/**
 * @author Rocket Bazaar Core Team
 *  Created at 27 May, 2016 6:01:28 PM
 */
namespace Magedelight\Rma\Block\Sellerhtml;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $authSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $collectionFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Rma\Model\Rma\Source\Status $rmaStatusSource,
        array $data = []
    ) {
        $this->_rmacollectionFactory = $collectionFactory;
        $this->authSession = $authSession;
        $this->_rmaStatusSouceModel = $rmaStatusSource;
        parent::__construct($context, $data);
    }
    /**
     * @return boolean
     */
    public function _construct()
    {
        // If no vendor found then return false;
        $vendorId = $this->authSession->getUser()->getVendorId();
        if (!$vendorId) {
            return false;
        }
        
        $returns = $this->_rmacollectionFactory->create()
            ->addFieldToSelect(['*'])
            ->addFieldToFilter('main_table.vendor_id', $vendorId);
        $returns->getSelect()->joinLeft(['v_order' =>'md_vendor_order'], 'main_table.order_id = v_order.order_id', ['vendor_order_id','v_order.increment_id as vendor_order_increment_id']);
        $returns->setOrder('date_requested', 'desc');
        $this->setReturns($returns);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->getBlock(
            'vendor.rma.pager'
        );
        $limit = $this->getRequest()->getParam('limit', false);
        $sfrm = $this->getRequest()->getParam('sfrm');
        $pager->setData('pagersfrm', 'new');
        $pager->setTemplate('Magedelight_Theme::html/pager.phtml');
        if ($sfrm != 'new' || !$limit) {
            $limit = 10;
            $pager->setPage(1);
        }
        $pager->setLimit($limit);
        $pager->setCollection($this->getReturns());
        $this->setChild('pager', $pager);
        $this->getReturns()->load();
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param type $_returnsrequest
     * @param type $tab
     * @return type
     */
    public function getViewUrl($_returnsrequest, $tab = '5,0')
    {
        return $this->getUrl('*/*/view', ['entity_id' => $_returnsrequest->getId(), 'tab' => $tab]);
    }
    
    /*
     * get Status label from status key
     */
    public function getStatusLabel($key)
    {
        return $this->_rmaStatusSouceModel->getItemLabel($key);
    }
}
