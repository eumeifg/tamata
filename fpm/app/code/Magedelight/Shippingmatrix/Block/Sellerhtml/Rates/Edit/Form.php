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
namespace Magedelight\Shippingmatrix\Block\Sellerhtml\Rates\Edit;

/**
 * Description of Offers
 *
 * @author Rocket Bazaar Core Team
 */
class Form extends \Magento\Framework\View\Element\Template
{

    protected $_matrixRateCollectionFactory;

    protected $_countryCollectionFactory;

    protected $_countryInformation;

    protected $_regionCollectionFactory;
    
    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate\CollectionFactory $matrixRateCollectionFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\Shippingmatrix\Model\Config\Source\Matrixrate $shippingMatrix
     * @param array $data
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate\CollectionFactory $matrixRateCollectionFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->_matrixRateCollectionFactory = $matrixRateCollectionFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_countryInformation = $countryInformation;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->authSession = $authSession;
        parent::__construct($context, $data);
    }

    public function getMatrixRateCollection()
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
        $collection = $this->_countryCollectionFactory->create();
        $countriesCollection = $collection->getData();
        foreach ($countriesCollection as $countryCollection) {
            $country = $this->_countryInformation->getCountryInfo($countryCollection['country_id']);
            $countries[] = ['country_id' => $countryCollection['country_id'], 'country_name' => $country->getFullNameLocale()];
        }
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

    public function getEditPostActionUrl()
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
}
