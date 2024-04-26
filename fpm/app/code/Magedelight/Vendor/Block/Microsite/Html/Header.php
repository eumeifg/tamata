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
namespace Magedelight\Vendor\Block\Microsite\Html;

class Header extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magedelight\Vendor\Model\Vendorrating
     */
    protected $_vendorRating;

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var \Magedelight\Vendor\Model\MicrositeFactory
     */
    protected $microsite;

    /**
     * @var \Magedelight\Vendor\Helper\Microsite\Image
     */
    protected $_helper;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Vendor\Model\Vendorrating $vendorRating
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param \Magedelight\Vendor\Model\MicrositeFactory $microsite
     * @param \Magedelight\Vendor\Helper\Microsite\Image $helper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Vendor\Model\Vendorrating $vendorRating,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Vendor\Model\MicrositeFactory $microsite,
        \Magedelight\Vendor\Helper\Microsite\Image $helper
    ) {
        $this->_vendorRating = $vendorRating;
        $this->_vendorFactory = $vendorFactory;
        $this->microsite = $microsite;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _prepareLayout()
    {
        $vendorData = $this->getVendorData();
        $vendor = $this->getVendor($this->getRequest()->getParam('vid'));

        if ($this->getRequest()->getActionName() != 'rating') {
            $this->pageConfig->getTitle()->set($vendor['business_name']);
            if (isset($vendorData['meta_keyword'])) {
                $this->pageConfig->setKeywords($vendorData['meta_keyword']);
            }
            if (isset($vendorData['meta_description'])) {
                $this->pageConfig->setDescription($vendorData['meta_description']);
            }
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                if (isset($vendor['business_name'])) {
                    $pageMainTitle->setPageTitle('Popular Products of ' . $vendor['business_name']);
                }
            }
        }

        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $baseUrl
            ]
        );
        /*$breadcrumbs->addCrumb(
            'seller_directory',
            [
                'label' => 'Seller Directory',
                'title' => 'Seller Directory',
                'link' => $baseUrl . 'rbvendor/sellerdirectory'
            ]
        );*/
        if (isset($vendor['business_name'])) {
            $breadcrumbs->addCrumb(
                'vendor',
                [
                    'label' => $vendor['business_name']
                ]
            );
        }

        parent::_prepareLayout();
    }

    /**
     * @param integer $vid
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVendor($vid = '')
    {
        $collection = $this->_vendorFactory->create()->getCollection()
            ->addFieldToFilter('main_table.vendor_id', ['eq' => $vid]);
        $collection->getSelect()->joinLeft(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = main_table.vendor_id AND rvwd.status = 1 AND rvwd.website_id = '
            . $this->_storeManager->getStore()->getWebsiteId()
        );
        return $collection->getFirstItem()->getData();
    }

    /**
     * @return float
     */
    public function getAvgRating()
    {
        $vendorId = $this->getRequest()->getParam('vid');
        $collection = $this->_vendorRating->getCollection()
            ->addFieldToFilter('vendor_id', $vendorId)->addFieldToFilter('is_shared', 1);
        $collection->getSelect()->joinLeft(
            ['rvrt' => 'md_vendor_rating_rating_type'],
            "main_table.vendor_rating_id = rvrt.vendor_rating_id",
            ['ROUND(SUM(`rvrt`.`rating_avg`)/(SELECT  count(*) FROM md_vendor_rating where (md_vendor_rating.vendor_id = main_table.vendor_id) AND (md_vendor_rating.is_shared = 1))) as rating_avg']
        );
        return $collection->getFirstItem()->getRatingAvg();
    }

    /**
     * @return \Magedelight\Vendor\Model\Microsite
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVendorData()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $vendorId = $this->getRequest()->getParam('vid');
        $vendorData = $this->microsite->create()->getCollection()
                        ->addFieldToFilter('vendor_id', $vendorId)
                        ->addFieldToFilter('store_id', $storeId)
                        ->getFirstItem();
        return $vendorData;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return string
     */
    public function getVendorRatingsUrl()
    {
        $vendorId = $this->getRequest()->getParam('vid');
        // Remove special char from vendor id.
        $vendorId = preg_replace('/\D/', '', $vendorId);
        return $this->getUrl('rbvendor/microsite_vendor/rating/', ['vid' => $vendorId]);
    }

    /**
     * @return string
     */
    public function getVendorProductCollnUrl()
    {
        $vendorId = $this->getRequest()->getParam('vid');
        return $this->getUrl('rbvendor/microsite_vendor/product/', ['vid' => $vendorId]);
    }

    /**
     * @return string
     */
    public function getVendorHomeUrl()
    {
        $vendorId = str_replace('/', '', $this->getRequest()->getParam('vid'));
        return $this->getUrl('rbvendor/microsite_vendor/', ['vid' => $vendorId]);
    }

    /**
     * Get reviews collection
     *
     * @return array
     */
    public function getVendorReviewsCollection()
    {
        $vendorId = $this->getRequest()->getParam('vid');
        $collection = $this->_vendorRating->getCollection()->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('is_shared', 1);
        $collection->getSelect()->joinLeft(
            ['rvrt' => 'md_vendor_rating_rating_type'],
            "main_table.vendor_rating_id = rvrt.vendor_rating_id",
            [
                'ROUND(SUM(`rvrt`.`rating_avg`)) as rating_avg'
            ]
        )->group('main_table.vendor_rating_id')->order('main_table.created_at DESC');
        return $collection->getData();
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
    }

    /**
     * @param string $image
     * @param integer $width
     * @param integer $height
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resize($image, $width = null, $height = null)
    {
        return $this->_helper->resize($image, $width, $height);
    }
}
