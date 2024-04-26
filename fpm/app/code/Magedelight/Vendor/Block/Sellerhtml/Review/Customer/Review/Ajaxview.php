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
namespace Magedelight\Vendor\Block\Sellerhtml\Review\Customer\Review;

use Magento\Framework\Exception\NoSuchEntityException;

class Ajaxview extends \Magento\Framework\View\Element\Template
{
    protected $_ratingFactory;

    private $_vendorFrontRatingTypeFactory;

    protected $_vendorRatingFactory;

    protected $_customerRepository;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magedelight\Vendor\Model\Vendorfrontratingtype $vendorfrontratingtypeFactory
     * @param \Magedelight\Vendor\Model\Vendorrating $vendorRatingFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magento\Framework\Registry $registry,
        \Magedelight\Vendor\Model\Vendorfrontratingtype $vendorfrontratingtypeFactory,
        \Magedelight\Vendor\Model\Vendorrating $vendorRatingFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->_ratingFactory = $ratingFactory;
        $this->vendorOrder = $vendorOrderFactory->create();
        $this->_vendorFrontRatingTypeFactory = $vendorfrontratingtypeFactory;
        $this->_vendorRatingFactory =  $vendorRatingFactory;
        $this->_customerRepository = $customerRepository;
        parent::__construct($context, $data);
    }

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
    public function getVendorRatingId()
    {
        return $this->getRequest()->getParam('vendor_rating_id', false);
    }

    public function getVendorFrontRatingType()
    {
        $vendorData = $this->_vendorFrontRatingTypeFactory
                ->getCollection()
                ->addFieldToFilter('vendor_rating_id', $this->getVendorRatingId());
        return $vendorData;
    }

    public function getRatingLables()
    {
        $ratingLables = [];
        foreach ($this->getRatings() as $_rating):
                $ratingLables[] = $this->escapeHtml($_rating->getRatingCode());
        endforeach;

        return $ratingLables;
    }

    public function getRatingValue()
    {
        $ratingValues = [];
        foreach ($this->getVendorFrontRatingType() as $_ratingType):
            $ratingValues[] = $_ratingType->getRatingValue();
        endforeach;
        return $ratingValues;
    }
    public function getRatingData()
    {
        $data = $this->_vendorRatingFactory->load($this->getVendorRatingId());
        return $data;
    }

    public function getCustomName()
    {
        try {
            $customer = $this->_customerRepository->getById($this->getRatingData()->getCustomerId());
            return $this->escapeHtml($customer->getFirstname()) . ' ' . $this->escapeHtml($customer->getLastname());
        } catch (NoSuchEntityException $ex) {
            return "Guest";
        }
    }

    public function getVendorOrderId()
    {
        return $this->getRatingData()->getVendorOrderId();
    }

    public function getComment()
    {
        return $this->getRatingData()->getComment();
    }
    public function getIncrementId($id)
    {
        $collection = $this->vendorOrder->getCollection()->addFieldToFilter('vendor_order_id', $id)->getFirstItem();
        return $collection['increment_id'];
    }
}
