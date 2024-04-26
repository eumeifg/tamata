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
namespace Magedelight\Vendor\Block\Microsite;

use Magento\Framework\View\Element\Template;

/**
 * Description of VendorRating
 *
 * @author Rocket Bazaar Core Team
 */
class VendorRating extends Template
{
    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\CollectionFactory
     */
    protected $_reviewCollectionFactory;

    /**
     * VendorRating constructor.
     * @param Template\Context $context
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\CollectionFactory $reviewCollectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\CollectionFactory $reviewCollectionFactory,
        array $data = []
    ) {
        $this->_reviewCollectionFactory = $reviewCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|void
     */
    protected function _construct()
    {
        parent::_construct();
        if (!($vendorId =  $this->getRequest()->getParam('vid'))) {
            return false;
        }
        $collection = $this->_reviewCollectionFactory->create();

        $collection->getSelect()->join(
            ['rvo' => 'md_vendor_order'],
            'main_table.vendor_order_id = rvo.vendor_order_id',
            ['increment_id']
        );
        $sort = $this->getRequest()->getParam('sort');
        $collection->addFieldToFilter('main_table.vendor_id', ['eq' => $this->getRequest()->getParam('vid')]);
        $collection->addFieldToFilter('is_shared', ['eq' => 1 ]);
        if ($sort == 'low_rate') {
            $collection->getSelect()->order('rating_avg ASC');
        } elseif ($sort == 'high_rate') {
            $collection->getSelect()->order('rating_avg DESC');
        } elseif ($sort == 'new') {
            $collection->getSelect()->order('created_at DESC');
        } elseif ($sort == 'old') {
            $collection->getSelect()->order('created_at ASC');
        }
        $this->setCollection($collection);
    }

    /**
     * @return $this|Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Pager */
        $pager = $this->getLayout()->createBlock(
            \Magento\Theme\Block\Html\Pager::class,
            'vendor.micro.rating.list.pager'
        );
        $limit = $this->getRequest()->getParam('limit', false);
        if (!$limit) {
            $limit = 8;
        }
        $pager->setLimit($limit)
            ->setShowAmounts(false)
            ->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();

        return $this;
    }

    /**
     * @return bool
     */
    public function getAllcollection()
    {
        if (!($vendorId =  $this->getRequest()->getParam('vid'))) {
            return false;
        }
        $collection = $this->_reviewCollectionFactory->create();

        $collection->getSelect()->join(
            ['rvo' => 'md_vendor_order'],
            'main_table.vendor_order_id = rvo.vendor_order_id',
            ['increment_id']
        );

        $collection->addFieldToFilter('main_table.vendor_id', ['eq' => $this->getRequest()->getParam('vid')]);
        $collection->addFieldToFilter('is_shared', ['eq' => 1 ]);
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
     * @param $val
     * @return false|string
     */
    public function getDateFromate($val)
    {
        $value = date('M d,Y', strtotime($val));
        return $value;
    }

    /**
     * @param $rat
     * @return string
     */
    public function getRatingText($rat)
    {
        if ($rat >= 80) {
            return 'Excellent';
        } elseif ($rat >= 60 && $rat <= 79) {
            return 'Very good';
        } elseif ($rat >= 40 && $rat <= 59) {
            return 'Good';
        } elseif ($rat >= 20 && $rat <= 39) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->getUrl('rbvendor/microsite_vendor/rating/vid/' . $this->getRequest()->getParam('vid'));
    }
}
