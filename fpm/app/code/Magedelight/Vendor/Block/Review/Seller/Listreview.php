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
namespace Magedelight\Vendor\Block\Review\Seller;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Listreview extends \Magento\Customer\Block\Account\Dashboard
{
    protected $_vendorratingFactory;

    protected $_vendorfrontratingtypeFactory;

    const PAGING_LIMIT = 10;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Vendor\Model\VendorratingFactory $vendorratingFactory
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magedelight\Vendor\Model\VendorfrontratingtypeFactory $vendorfrontratingtypeFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Vendor\Model\VendorratingFactory $vendorratingFactory,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magedelight\Vendor\Model\VendorfrontratingtypeFactory $vendorfrontratingtypeFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
        $this->_vendorratingFactory = $vendorratingFactory;
        $this->vendorOrder = $vendorOrderFactory->create();
        $this->_vendorfrontratingtypeFactory = $vendorfrontratingtypeFactory;
    }

    /**
     * Get html code for toolbar
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * @param $date
     * @return string
     */
    public function dateFormat($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::SHORT);
    }

    /**
     * @param $vid
     * @return int
     */
    public function getReviewData($vid)
    {
        $collection = $this->_vendorfrontratingtypeFactory->create()->getCollection()->addFieldToFilter(
            'vendor_rating_id',
            $vid
        );
        $sum = 0;
        foreach ($collection as $vdata) {
            $sum += $vdata['rating_avg'];
        }
        return $sum;
    }

    /**
     * @param $vOrderId
     * @return mixed
     */
    public function getIncrementId($vOrderId)
    {
        $collection = $this->vendorOrder->getCollection()->addFieldToFilter('vendor_order_id', $vOrderId);
        return $collection->getFirstItem()->getIncrementId();
    }

    /**
     * Initializes toolbar
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        if ($this->getReviews()) {
            $toolbar = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'customer_review_list.toolbar'
            )->setCollection(
                $this->getReviews()
            );

            $this->setChild('toolbar', $toolbar);
        }
        return parent::_prepareLayout();
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getReviews()
    {
        /*get values of current page*/
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;

        /*get values of current limit*/
        $pageSize = ($this->getRequest()->getParam('limit')) ?
            $this->getRequest()->getParam('limit') : self::PAGING_LIMIT;

        $customerId = $this->customerSession->getCustomerId();
        $collection = $this->_vendorratingFactory->create()->getCollection();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->getSelect()->joinLeft(
            ['rvwd'=>'md_vendor_website_data'],
            "main_table.vendor_id = rvwd.vendor_id",
            [
                'rvwd.name',
                'rvwd.business_name'
            ]
        );

        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }
}
