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
namespace Magedelight\Vendor\Block\Sellerhtml\Form;

use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magedelight\Vendor\Model\Vendor;

/**
 * Class vendor Register
 */
class Register extends \Magento\Directory\Block\Data
{

    /**
     * @var \Magento\Directory\Model\Country\Postcode\ConfigInterface
     */
    protected $postCodesConfig;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magedelight\Vendor\Model\Source\Status
     */
    protected $vendorStatus;

    /**
     * @var \Magedelight\Theme\Model\Source\Region
     */
    protected $region;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Url
     */
    protected $_vendorUrl;
    /**
     * Category collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     *
     * @var int[]
     */
    protected $selectedCats;

    /**
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface;
     */
    protected $scopeConfig;

    /**
     *
     * @var \Magedelight\Vendor\Model\Request
     */
    protected $vendorStatusRequest;

    public $storeManager;
    /**
     * @var \Magedelight\Vendor\Model\Source\RequestTypes
     */
    public $requestTypes;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magedelight\Vendor\Model\VendorRepository
     */
    protected $vendorRepository;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\Backend\Model\Url $vendorUrl
     * @param \Magento\Framework\Registry $registry
     * @param \Magedelight\Theme\Model\Source\Frontend\Region $region
     * @param VendorStatus $vendorStatus
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magedelight\Vendor\Model\RequestFactory $vendorStatusRequestFactory
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magento\Directory\Model\Country\Postcode\ConfigInterface $postCodesConfig
     * @param \Magedelight\Vendor\Model\Source\RequestTypes $requestTypes
     * @param \Magedelight\Vendor\Model\VendorRepository $vendorRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magedelight\Backend\Model\Url $vendorUrl,
        \Magento\Framework\Registry $registry,
        \Magedelight\Theme\Model\Source\Frontend\Region $region,
        VendorStatus $vendorStatus,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magedelight\Vendor\Model\RequestFactory $vendorStatusRequestFactory,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magento\Directory\Model\Country\Postcode\ConfigInterface $postCodesConfig,
        \Magedelight\Vendor\Model\Source\RequestTypes $requestTypes,
        \Magedelight\Vendor\Model\VendorRepository $vendorRepository,
        array $data = []
    ) {
        $this->_vendorUrl = $vendorUrl;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->registry = $registry;
        $this->region = $region;
        $this->vendorStatus = $vendorStatus;
        $this->scopeConfig = $context->getScopeConfig();
        $this->vendorStatusRequest = $vendorStatusRequestFactory->create();
        $this->date = $date;
        $this->vendorHelper = $vendorHelper;        
        $this->storeManager = $context->getStoreManager();
        $this->requestTypes = $requestTypes;
        $this->postCodesConfig = $postCodesConfig;
        $this->authSession = $authSession;
        $this->vendorRepository = $vendorRepository;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->unsetValue();
        return parent::_prepareLayout();
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->_vendorUrl->getRegisterPostUrl() . 'rbhash/' . $this->getVendor()->getEmailVerificationCode();
    }

    /**
     * Retrieve Categories List
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategories()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->_categoryCollectionFactory->create()->addAttributeToFilter(
            'entity_id',
            ['eq' => $this->storeManager->getStore()->getRootCategoryId()]
        );
        $collection->addAttributeToSelect('name')->addRootLevelFilter()->load();

        foreach ($collection as $category) {
            return $this->_getTreeCategories($category, false);
        }
    }

    /**
     * @param $parent
     * @param $isChild
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getTreeCategories($parent, $isChild)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->_categoryCollectionFactory->create();
        $vendor = $this->getVendor();
        $vendorCats = $vendor->getCategory();
        ($vendorCats === null) ? $vendorCats = [] : $vendorCats;
        $collection->addAttributeToSelect('name')
           ->addAttributeToFilter('is_active', '1')
           ->addAttributeToFilter('include_in_menu', '1')
           ->addAttributeToFilter('parent_id', ['eq' => $parent->getId()])
           ->addAttributeToFilter('entity_id', ['neq' => $parent->getId()])
           ->addAttributeToSort('position', 'asc')
           ->load();
        $currentlevel = $parent->getLevel() + 1;

        $ulClasses = ($currentlevel > 2) ? " submenu" : "";
        $html = '<ul class="category-ul level-' . $currentlevel . ' ' . $ulClasses . '">';
        foreach ($collection as $category) {
            $addSelectbtn = false;
            $class = 'level-' . $category->getLevel();
            if ($category->getLevel() > $currentlevel) {
                $html .= '<ul class="' . $class . '">';
            } elseif ($category->getLevel() < $currentlevel) {
                $html .= '</ul><ul class="' . $class . '">';
            }

            $childClass = '';
            if ($category->hasChildren()) {
                $childClass = ' expand';
                if ($category->getLevel() == 3) {
                    $childClass .= ' has-children sub-cat-parent';
                } else {
                    $childClass .= ($category->getLevel() == 2) ? ' base has-children' : ' has-children';
                }
                $addSelectbtn = true;
            }

            $html .= '<li class="item level-' . $category->getLevel() . $childClass . '">';
            if (!$category->hasChildren() && !($vendorCats === null)) {
                $checked = in_array($category->getId(), $vendorCats, true) ? ' checked = "checked"' : '';
                $html .= '<input type="checkbox" name="category[]" id="category-' . $category->getId() . '" ';
                $html .= 'value="' . $category->getId() . '" title="' . $category->getName() . '" ';
                $html .= 'class="checkbox"' . $checked . '/>';
            }
            $html .= '<label class="label cat-collapse" for="category-' . $category->getId() . '">';
            $html .= '<span>' . $category->getName() . '</span></label>';
            if ($addSelectbtn) {
                $html .= '<input type="checkbox" name="selectall" id="slt-' . $category->getId() . '" value="';
                $html .= $category->getId() . '" title="' . $category->getName() . '" class="checkbox slt-chk" />';
                $html .= '<label class="label selectall-subcat" for="slt-' . $category->getId() . '"><span>';
                $html .= __('Select All ') . '<strong>' . $category->getName() . '</strong>' . '</span></label>';
            }

            $currentlevel = $category->getLevel();
            if ($category->hasChildren()) {
                $html .= $this->_getTreeCategories($category, true);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     *
     * @return \Magedelight\Vendor\Model\Vendor
     */
    public function getVendor()
    {
        $vendor = $this->registry->registry('md_vendor');
        if ($this->authSession->getVendorPostData()) {
            $vendor->setData($this->authSession->getVendorPostData());
        }
        $this->authSession->unsVendorPostData();
        return $vendor;
    }

    /**
     * get default country code
     * @return string
     */
    public function getDefaultCountryCode()
    {
        return $this->region->getDefaultCountry();
    }

    /**
     * get default country code
     * @return string
     */
    public function getDefaultCountryLabel()
    {
        return $this->region->getDefaultCountryLabel();
    }

    /**
     * retrun region options for default country
     * @return array
     */
    public function getRegions()
    {
        return $this->region->getOptions();
    }

    /**
     * @return bool
     */
    public function isRegionsRequired()
    {
        return $this->region->getIsRegionRequired($this->getDefaultCountryCode());
    }

    /**
     * @return bool
     */
    public function isZipRequired()
    {
        return $this->region->getIsZipRequired($this->getDefaultCountryCode());
    }

    /**
     * @return false|string
     */
    public function getPostcodes()
    {
        return json_encode(["postCodes"=>$this->postCodesConfig->getPostCodes()], JSON_HEX_TAG);
    }

    /**
     * get label for vendor status
     * @return string
     */
    public function getVendorStatusLabel()
    {
        return $this->vendorStatus->getOptionText($this->getVendor()->getStatus());
    }

    /**
     *
     * @return boolean
     */
    public function isVendorStatusPending()
    {
        return ($this->getVendor()->getStatus() === VendorStatus::VENDOR_STATUS_PENDING);
    }

    /**
     * @return mixed
     */
    public function getTermsadnCondition()
    {
        return $this->scopeConfig->getValue(
            'vendor/terms_conditions/editortextarea',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return \Magedelight\Vendor\Helper\Data
     */
    public function vendorHelper()
    {
        return $this->vendorHelper;
    }

    /**
     *
     * @param string $field
     * @param string $type
     * @return boolean
     */
    public function isRemoved($field = '', $type = '')
    {
        return $this->vendorHelper->isRemoved($field, $type);
    }

    /**
     *
     * @return string
     */
    public function getLoggedUserName()
    {
        return $this->authSession->getUser()->getName();
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        if ($this->getVendor()->getLogo() != null && $this->getVendor()->getLogo() != '') {
            return $this->_vendorUrl->getMediaUrl() . 'vendor/logo' . $this->getVendor()->getLogo();
        }
        $placeholder = self::DEFAULT_AVATAR;
        $configLogo = $this->scopeConfig->getValue(self::DEFAULT_AVATAR_PATH);
        if ($configLogo != null && $configLogo != '') {
            $placeholder = $configLogo;
        }
        return $this->_vendorUrl->getMediaUrl() . 'vendor/logo/' . $placeholder;
    }

    /**
     * @return string
     */
    public function getLogoWidth()
    {
        return $this->vendorHelper->getConfigValue('vendor/general/company_logo_width');
    }

    /**
     * @return mixed
     */
    public function getLogoHeight()
    {
        return $this->vendorHelper->getConfigValue('vendor/general/company_logo_height');
    }

    /**
     *
     * @return boolean
     */
    public function isBankingInfoEnabled()
    {
        return $this->vendorHelper->getConfigValue(Vendor::IS_ENABLED_BANKING_DETAILS_XML_PATH);
    }

    /**
     *
     * @return boolean
     */
    public function isBankingInfoOptional()
    {
        return $this->vendorHelper->getConfigValue(Vendor::IS_BANK_DETAILS_OPTIONAL_XML_PATH);
    }
}
