<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Block\Sellerhtml\Rates;

class Rate extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $_vendorProductFactory;

    /**
     * @var \Magento\Directory\Model\CountryInformationAcquirer
     */
    protected $countryInformationAcquirer;

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_productHelper;

    /**
     * @var \Magedelight\Shippingmatrix\Model\ShippingMethodFactory
     */
    protected $_shippingMethodFactory;
    protected $_matrixRateCollectionFactory;

    protected $_countryInformation;

    protected $_regionCollectionFactory;

    protected $_shippingMatrix;
    
    protected $_countryCollectionFactory;
    
    protected $directoryHelper;
    protected $_matrixrate;
    
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate\CollectionFactory $matrixRateCollectionFactory,
        \Magedelight\Shippingmatrix\Model\Carrier\Matrixrate $matrixrate,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magedelight\Shippingmatrix\Model\Config\Source\Matrixrate $shippingMatrix,
        \Magedelight\Shippingmatrix\Model\ShippingMethodFactory $shippingMethodFactory,
        \Magedelight\Catalog\Helper\Data $productHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magento\Directory\Model\CountryInformationAcquirer $countryInformationAcquirer,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_matrixRateCollectionFactory = $matrixRateCollectionFactory;
        $this->_matrixrate = $matrixrate;
        $this->_countryInformation = $countryInformation;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_shippingMatrix = $shippingMatrix;
        $this->directoryHelper = $directoryHelper;
        $this->_shippingMethodFactory = $shippingMethodFactory;
        $this->_productHelper = $productHelper;
        $this->_coreRegistry = $coreRegistry;
        $this->_vendorFactory = $vendorFactory;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->_vendorProductFactory = $vendorProductFactory;
        $this->_countryFactory = $countryFactory;
        $this->_storeManager = $storeManager;
        $this->authSession = $authSession;
        parent::__construct($context, $data);
    }

    public function getMatrixRateCollection($pager = true)
    {

        $vendorId = $this->authSession->getUser()->getVendorId();

        $collection = $this->_matrixRateCollectionFactory->create();
        
        $condition_name = $this->getShippingMatrixCondition();

        $collection->addFieldToFilter(`main_table`.'vendor_id', ['eq'=>$vendorId]);
        
        $collection->addFieldToFilter(`main_table`.'condition_name', ['eq'=>$condition_name]);
        $collection->addFieldToFilter(`main_table`.'website_id', ['eq'=> $this->_storeManager->getStore()->getWebsiteId()]);

        $collection->getSelect()->order('pk DESC');

        if ($collection && $pager) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'shippingmatrix.rates.pager');
            
            $limit = $this->getRequest()->getParam('limit', false);
            if (!$limit) {
                $limit = 10;
                $pager->setPage(1);
            }
            
            $pager->setLimit($limit)->setCollection($collection);
            $pager->setAvailableLimit([10=>10,20=>20,50=>50]);
            $this->setChild('pager', $pager);
        }
        
        return $collection;
    }

    public function getCountryName($countryId)
    {
        $country = $this->_countryFactory->create()->loadByCode($countryId);
        return $country->getName();
    }

    public function getRegionName($regionId)
    {
        $collection = $this->_regionCollectionFactory->create();
        
        $regions = $collection->getData();

        $regionName = '';

        foreach ($regions as $region) {
            if ($region['region_id'] == $regionId) {
                $regionName = $region['name'];
            }
        }

        return $regionName;
    }

    public function getRegionId($countryId = '', $regionCode = '')
    {
        $regionId = [];

        $collection = $this->_regionCollectionFactory->create();

        $regions = $collection->getData();

        foreach ($regions as $region) {
            if ($region['country_id'] == $countryId && $region['code'] == "$regionCode") {
                $regionId = $region['region_id'];
            }
        }
        
        return $regionId;
    }

    public function getEditPostActionUrl($matrixRateId)
    {
        return $this->getUrl("rbshippingmatrix/rates/edit/id/$matrixRateId");
    }
    
    public function getShippingConditionName($condition)
    {
        $collections = $this->_shippingMatrix->toOptionArray();
        $conditionName = '';
        foreach ($collections as $collection) {
            if ($collection['value'] == $condition) {
                $conditionName = $collection;
            }
        }
        
        return $conditionName;
    }
    
    public function getMatrixRate()
    {

        $matrixRateId = (int)$this->getRequest()->getParam('id');

        $collection = $this->_matrixRateCollectionFactory->create();

        $collection->addFieldToFilter('pk', ['eq'=>$matrixRateId]);

        $matrixRateCollection = $collection->getData();

        $matrixRated = '';

        foreach ($matrixRateCollection as $matrixrate) {
            $matrixRated = $matrixrate;
        }
        
        return $matrixRated;
    }
 
    public function getCountryCollection()
    {
        $countries = [];

        $collection = $this->directoryHelper->getCountryCollection();
        
        foreach ($collection as $country) {
            $countries[] = ['country_id' => $country->getCountryId(), 'country_name' => $country->getName()];
        }
        
        usort($countries, function ($a, $b) {
            return strcmp($a["country_name"], $b["country_name"]);
        });

        return $countries;
    }

    public function getRegionCollection($countryId = '')
    {
        $countryRegion = [];

        $collection = $this->_regionCollectionFactory->create();

        $regions = $collection->getData();

        foreach ($regions as $region) {
            if ($region['country_id'] == $countryId) {
                $countryRegion[] = $region;
            }
        }
        
        return $countryRegion;
    }

    public function getSavePostActionUrl()
    {
        return $this->getUrl('rbshippingmatrix/rates/save');
    }

    public function getLoggedInVendorId()
    {
        $vendorId = $this->authSession->getUser()->getVendorId();
        return $vendorId;
    }

    public function getShippingMatrixCondition()
    {
        $website = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'));
        
        $conditionName = $website->getConfig('carriers/rbmatrixrate/condition_name');
        
        return $conditionName;
    }
    
    public function getDeletePostActionUrl($matrixRateId)
    {
        return $this->getUrl("rbshippingmatrix/rates/delete/id/$matrixRateId");
    }
    
    public function getShippingMatrixConditionLabel()
    {
        $conditionName = $this->getShippingMatrixCondition();
        $conditionLabel = [];
        $label = $this->_matrixrate->getCode('condition_name_short', $conditionName);
        $labelFrom = $label.' From';
        $labelTo = $label.' To';
        $conditionLabel['condition_from'] = $labelFrom;
        $conditionLabel['condition_to'] = $labelTo;
        return $conditionLabel;
    }
    
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
    public function weightUnit()
    {
        return $this->directoryHelper->getWeightUnit();
    }
    
    public function getAllMatrixRateCollection()
    {
        $vendor = $this->authSession->getUser();
        $collection = $this->_matrixRateCollectionFactory->create();
        ($vendor && $vendor->getVendorId())?$collection->addFieldToFilter('vendor_id', $vendor->getVendorId()):'';
        $condition_name = $this->getShippingMatrixCondition();
        $collection->addFieldToFilter('condition_name', ['eq'=>$condition_name]);
        $collection->getSelect()->order('pk DESC');
        $collection->getSelect()->limit(5);
        return $collection;
    }
    
    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getShippingMethods()
    {
        $collection = $this->_shippingMethodFactory->create()->getCollection();
        $result = [];
        $result[0]= ['value'=>'','label'=>__('--Select--')];
        if ($collection->getSize() > 0) {
            foreach ($collection as $shippingMethod) {
                $result[] = ['value' => $shippingMethod->getShippingMethodId(), 'label' => $shippingMethod->getShippingMethod()];
            }
        }
        return $result;
    }
    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getShippingMethodById($id)
    {
        if ($id) {
            $collection = $this->_shippingMethodFactory->create()->getCollection()->addFieldToFilter('shipping_method_id', $id)->getFirstItem();
            if ($collection->getShippingMethodId()) {
                return $collection;
            }
        }
    }
    
    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getShippingMethodNameById($id)
    {
        $shippingMethod = $this->getShippingMethodById($id);
        return ($shippingMethod)?$shippingMethod->getShippingMethod():'---';
    }
}
