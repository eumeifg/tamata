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

/**
 * Description of AbstractRatings
 *
 * @author Rocket Bazaar Core Team
 */
abstract class AbstractRatings extends \Magedelight\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\CollectionFactory
     */
    protected $_reviewCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
    protected $reviews;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Vendor\Model\Vendorrating $vendorRating
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\CollectionFactory $reviewCollectionFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magedelight\Vendor\Model\Vendorrating $vendorRating,
        \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\CollectionFactory $reviewCollectionFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        array $data = []
    ) {
        $this->_reviewCollectionFactory = $reviewCollectionFactory;
        $this->_vendorRating = $vendorRating;
        $this->_orderConfig = $orderConfig;
        $this->_date = $date;
        $this->authSession = $authSession;
        parent::__construct($context, $data);
    }

    public function getCustomerReviews()
    {
        if (!($vendorId = $this->authSession->getUser()->getVendorId())) {
            return false;
        }

        $collection = $this->_reviewCollectionFactory->create();

        $collection->getSelect()->join(
            ['rvo' => 'md_vendor_order'],
            'main_table.vendor_order_id = rvo.vendor_order_id',
            ['increment_id']
        );

        $collection->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId]);
        $collection->addFieldToFilter('is_shared', ['eq' => 1 ]);

        if ($q = $this->getRequest()->getParam('q', false)) {
            $collection->addFieldToFilter(
                ['main_table.vendor_rating_id', 'rvo.increment_id','oce.firstname','oce.lastname'],
                [
                    ['like' => '%' . $q . '%'],
                    ['like' => '%' . $q . '%'],
                    ['like' => '%' . $q . '%'],
                    ['like' => '%' . $q . '%']
                ]
            );
        }
        $data = $this->getRequest()->getParams();

        if (!empty($data)) {
            if ($this->getRequest()->getParam('id')['from']) {
                $collection->addFieldToFilter(
                    'main_table.vendor_rating_id',
                    ['gteq' => $this->getRequest()->getParam('id')['from']]
                );
            }
            if ($this->getRequest()->getParam('id')['to']) {
                $collection->addFieldToFilter(
                    'main_table.vendor_rating_id',
                    ['lteq' => $this->getRequest()->getParam('id')['to']]
                );
            }

            if ($this->getRequest()->getParam('vendor_order_id')['from']) {
                $collection->addFieldToFilter(
                    'rvo.increment_id',
                    ['gteq' => $this->getRequest()->getParam('vendor_order_id')['from']]
                );
            }
            if ($this->getRequest()->getParam('vendor_order_id')['to']) {
                $collection->addFieldToFilter(
                    'rvo.increment_id',
                    ['lteq' => $this->getRequest()->getParam('vendor_order_id')['to']]
                );
            }

            if ($this->getRequest()->getParam('ratings')['from']) {
                $collection->addFieldToFilter(
                    'rating_avg',
                    ['gteq' => $this->getRequest()->getParam('ratings')['from']]
                );
            }
            if ($this->getRequest()->getParam('ratings')['to']) {
                $collection->addFieldToFilter(
                    'rating_avg',
                    ['lteq' => $this->getRequest()->getParam('ratings')['to']]
                );
            }

            if ($this->getRequest()->getParam('created_at')['from']) {
                $dateFrom = $this->_date->gmtDate(null, $this->getRequest()->getParam('created_at')['from']);
                $collection->addFieldToFilter(
                    'main_table.created_at',
                    ['gteq' => $dateFrom]
                );
            }
            if ($this->getRequest()->getParam('created_at')['to']) {
                $dateTo = $this->_date->gmtDate(null, $this->getRequest()->getParam('created_at')['to']);
                $collection->addFieldToFilter(
                    'main_table.created_at',
                    ['lteq' => $dateTo]
                );
            }
            if ($this->getRequest()->getParam('customer_name', false)) {
                $customerName = $this->getRequest()->getParam('customer_name');

                $collection->addFieldToFilter(
                    ['oce.firstname', 'oce.lastname'],
                    [
                    ['like' => '%' . trim($customerName) . '%'],
                    ['like' => '%' . trim($customerName) . '%']

                    ]
                );
            }
        }

        $sortOrder = $this->getRequest()->getParam('sort_order', 'main_table.created_at');
        $direction = $this->getRequest()->getParam('dir', 'DESC');
        $this->_addSortOrderToCollection($collection, $sortOrder, $direction);

        $this->setCollection($collection);
        return $collection;
    }

    protected function _addSortOrderToCollection($collection, $sortOrder, $direction)
    {
        $collection->getSelect()->order($sortOrder . ' ' . $direction);
    }
}
