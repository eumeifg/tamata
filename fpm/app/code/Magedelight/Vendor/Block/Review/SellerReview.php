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
namespace Magedelight\Vendor\Block\Review;

class SellerReview extends \Magento\Framework\View\Element\Template
{
    private $_registry;

    protected $customerSession;

    protected $_ratingFactory;

    protected $_vendorrating;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magedelight\Vendor\Model\VendorratingFactory $vendorrating
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magedelight\Vendor\Model\VendorratingFactory $vendorrating,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);       
        $this->_ratingFactory = $ratingFactory;
        $this->_vendorrating = $vendorrating;
        $this->customerSession = $customerSession;
        $this->_registry = $registry;
    }

    /**
     * @return mixed
     */
    public function getcustomerSession()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomer()->getData();
        }
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRatings()
    {
        return $this->_ratingFactory->create()->getResourceCollection()->addEntityFilter(
            'vendor'
        )->setPositionOrder()->addRatingPerStoreName(
            $this->_storeManager->getStore()->getId()
        )->setStoreFilter(
            $this->_storeManager->getStore()->getId()
        )->setActiveFilter(
            true
        )->load()->addOptionToItems();
    }

    /**
     * @return mixed
     */
    public function currentOrderStatus()
    {
        $orderData = $this->_registry->registry('current_order');
        return $orderData->getStatus();
    }

    /**
     * @return mixed
     */
    public function currentOrderID()
    {
        $orderData = $this->_registry->registry('current_order');
        return $orderData->getId();
    }

    /**
     * @return bool
     */
    public function checkIdExist()
    {
        $orderData = $this->_registry->registry('current_order');
        $collection = $this->_vendorrating->create()->getCollection()
             ->addFieldToFilter('vendor_order_id', $orderData->getId());
        if (empty($collection->getData())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getVendor_id()
    {
        $id = $this->getRequest()->getParam('id');
        return $id;
    }

    /**
     * @return mixed
     */
    public function getIncrementId()
    {
        $increment_id = $this->getRequest()->getParam('increment_id');
        return $increment_id;
    }

    /**
     * @return mixed
     */
    public function getFlag()
    {
        $flag = $this->getRequest()->getParam('flag');
        return $flag;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        return $order_id;
    }
}
