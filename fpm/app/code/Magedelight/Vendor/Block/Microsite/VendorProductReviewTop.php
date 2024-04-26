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

use Magento\Store\Model\Store;

/**
 * Description of VendorProductReviewTop
 *
 * @author Rocket Bazaar Core Team
 */
class VendorProductReviewTop extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var \Magedelight\Vendor\Model\MicrositeFactory
     */
    protected $microsite;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\CollectionFactory
     */
    protected $_reviewCollectionFactory;

    /**
     * VendorProductReviewTop constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param \Magedelight\Vendor\Model\MicrositeFactory $microsite
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\CollectionFactory $reviewCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Vendor\Model\MicrositeFactory $microsite,
        \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\CollectionFactory $reviewCollectionFactory,
        array $data = []
    ) {
        $this->_vendorFactory = $vendorFactory;
        $this->microsite = $microsite;
        $this->_reviewCollectionFactory = $reviewCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\Collection
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
    public function getCurrentUrl()
    {
        return $this->getUrl('rbvendor/microsite_vendor/rating/vid/' . $this->getRequest()->getParam('vid'));
    }

    /**
     * @return string
     */
    public function getVendorLogo()
    {
        $vid = $this->getRequest()->getParam('vid');
        $vname = $this->getVendor($vid);
        $unsecureBaseURL = $this->_scopeConfig->getValue(Store::XML_PATH_UNSECURE_BASE_URL, 'default');
        $mediaDirectory = $unsecureBaseURL . 'pub/media/';
        if ($vname['logo']) {
            return $mediaDirectory . 'vendor/logo' . $vname['logo'];
        } else {
            return '';
        }
    }

    /**
     * @param $vid
     * @return mixed
     */
    public function getVendor($vid)
    {
        $collection = $this->_vendorFactory->create()->getCollection()
            ->addFieldToFilter('vendor_id', ['eq' => $vid])
            ->addFieldToFilter('status', ['eq' => 1]);
        return $collection->getFirstItem()->getData();
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getMicrositeData()
    {
        $vendorId = $this->getRequest()->getParam('vid');
        $vendorData = $this->microsite->create()->getCollection()
            ->addFieldToFilter('vendor_id', $vendorId)->getFirstItem();
        return $vendorData;
    }
}
