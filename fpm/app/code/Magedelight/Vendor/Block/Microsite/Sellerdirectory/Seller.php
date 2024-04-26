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
namespace Magedelight\Vendor\Block\Microsite\Sellerdirectory;

use Magedelight\Vendor\Model\Source\Status as VendorStatus;

/**
 * Description of Seller
 *
 * @author Rocket Bazaar Core Team
 */
class Seller extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $_vendorHelper;

    /**
     * @var \Magedelight\Vendor\Helper\Microsite\Image
     */
    protected $_helper;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\CollectionFactory
     */
    protected $_reviewCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $_categoryFlatState;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $_vendorcollectionFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var \Magedelight\Vendor\Helper\Microsite\Data
     */
    protected $_micrositeHelper;

    public $sortBy;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorcollectionFactory
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\CollectionFactory $reviewCollectionFactory
     * @param \Magedelight\Vendor\Helper\Data $_vendorHelper
     * @param \Magedelight\Vendor\Helper\Microsite\Image $helper
     * @param \Magedelight\Vendor\Helper\Microsite\Data $micrositeHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Vendor\Model\VendorFactory $vendorcollectionFactory,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Grid\CollectionFactory $reviewCollectionFactory,
        \Magedelight\Vendor\Helper\Data $_vendorHelper,
        \Magedelight\Vendor\Helper\Microsite\Image $helper,
        \Magedelight\Vendor\Helper\Microsite\Data $micrositeHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_vendorcollectionFactory = $vendorcollectionFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryFlatState = $categoryFlatState;
        $this->_reviewCollectionFactory = $reviewCollectionFactory;
        $this->_vendorHelper = $_vendorHelper;
        $this->_helper = $helper;
        $this->_micrositeHelper = $micrositeHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _addBreadcrumbs()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $baseUrl
            ]
        );
        /*$breadcrumbs->addCrumb(
            'md_microsite',
            [
                'label' => 'Seller Directory',
                'title' => 'Seller Directory',
                'link' => $baseUrl . 'microsite/sellerdirectory'
            ]
        );*/
    }

    /**
     * @return \Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        $pageTitle = 'Seller Directory';
        $this->_addBreadcrumbs();
        if ($pageTitle) {
            $this->pageConfig->getTitle()->set($pageTitle);
        }
        return parent::_prepareLayout();
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getVendorCollection()
    {
        $categoryId = $this->getRequest()->getParam('id');
        $this->sortBy = $this->getRequest()->getParam('sortorder');

        if (!empty($categoryId)) {
            return $this->getVendorByCategory($categoryId);
        }

        $collections = $this->_vendorcollectionFactory->create()
            ->getCollection()
            ->addFieldToFilter('email', ['neq' => \Magedelight\Vendor\Model\Vendor::ADMIN_VENDOR_EMAIL]);

        $userCheck = ($this->_vendorHelper->isModuleEnabled('Magedelight_User')) ?
            " and rvwd.is_user = 0" : "";
        $collections->getSelect()->join(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = main_table.vendor_id ' . $userCheck . ' and rvwd.status = '
            . VendorStatus::VENDOR_STATUS_ACTIVE,
            ['vendor_status'=>'rvwd.status',
                'vendor_business_name' => 'rvwd.business_name',
                'vendor_created_at' => 'created_at',
                'country_id' => 'country_id',
                'logo' => 'rvwd.logo']
        );

        if (!empty($this->sortBy)) {
            $collections->getSelect()->order('business_name ' . $this->sortBy);
        } else {
            $collections->getSelect()->order('business_name ASC');
        }

        return $collections;
    }

    /**
     * @param $categoryId
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getVendorByCategory($categoryId)
    {
        $categoryIds = $this->getSubcategoriesIds($categoryId);
        $vendors = $this->_vendorcollectionFactory->create()
            ->getCollection()
            ->addFieldToFilter('email', ['neq' => 'admin@gmail.com'])
            ->addFieldToFilter('status', ['eq' => VendorStatus::VENDOR_STATUS_ACTIVE]);

        $vendors->getSelect()->joinLeft(
            ['md_vendor_category'=>$vendors->getTable('md_vendor_category')],
            'main_table.vendor_id = md_vendor_category.vendor_id'
        );
        $vendors->addFieldToFilter('md_vendor_category.category_id', ['in'=>$categoryIds]);
        $vendors->getSelect()->group('main_table.vendor_id');
        return $vendors;
    }

    /**
     * @param $categoryId
     * @return array
     */
    public function getSubcategoriesIds($categoryId)
    {
        $category = $this->_categoryFactory->create()->load($categoryId);
        $categoryIds = explode(',', $category->getAllChildren());
        return $categoryIds;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrlSellerDirectory()
    {
        $path = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $path;
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getRatingAvg($vendorId)
    {
        $collection = $this->_reviewCollectionFactory->create();

        $collection->getSelect()->join(
            ['rvo' => 'md_vendor_order'],
            'main_table.vendor_order_id = rvo.vendor_order_id',
            ['increment_id']
        );

        $collection->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId]);
        $collection->addFieldToFilter('is_shared', ['eq' => 1 ]);

        return $collection->getFirstItem()->getRatingAvg();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVendorUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . 'vendor/logo/';
    }

    /**
     *
     * @return Vendor Name
     */
    public function getVendorName($vendorId)
    {
        return $this->_vendorHelper->getVendorNameById($vendorId);
    }

    /*pass imagename, width and height*/
    /**
     * @param $image
     * @param null $width
     * @param null $height
     * @return bool|\Magento\Framework\UrlInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resize($image, $width = null, $height = null)
    {
        return $this->_helper->resize($image, $width, $height);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPlaceholderImage()
    {
        return $this->_storeManager->getStore()->getConfig('catalog/placeholder/small_image_placeholder');
    }

    /**
     * @return vendor microsite url
     */
    public function getVendorMicrositeUrl($vendorId)
    {
        return $this->_micrositeHelper->getVendorMicrositeUrl($vendorId);
    }

    /**
     * @return mixed
     */
    public function getSellerDirectoryBannerImagePath()
    {
        return $this->_vendorHelper->getConfigValue('rbmicrosite/settings/sellerdirectory_banner_image');
    }

    /**
     * @return mixed
     */
    public function getVendorMicrositeDescription()
    {
        return $this->_vendorHelper->getConfigValue('rbmicrosite/settings/sellerdirectory_description');
    }
}
