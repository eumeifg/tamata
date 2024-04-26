<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Block\Sellerhtml;

use Magedelight\Catalog\Model\ResourceModel\ProductRequestStore\CollectionFactory as ProductRequestStoreCollectionFactory;
use Magedelight\Catalog\Model\ResourceModel\ProductRequestWebsite\CollectionFactory as ProductRequestWebsiteCollectionFactory;

abstract class AbstractProduct extends \Magedelight\Backend\Block\Template
{
    const DEFAULT_ATTRIBUTE_SET_ID = 4;

    const XML_PATH_EXCLUDE_ATTRIBUTES = 'vendor_product/vital_info/attributes';

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $_vendorProductFactory;

    /**
     * @var \Magedelight\Catalog\Model\Source\Condition
     */
    protected $_productCondition;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * @var \Magento\Framework\Json\Decoder
     */
    protected $_jsonDecoder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     *
     * @var array
     */
    protected $vitalAttributes;

    /**
     *
     * @var array
     */
    protected $additionalAttributes;

    /**
     *
     * @var array
     */
    protected $variantAttributes;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\Catalog\Model\PlaceholderFactory
     */
    protected $placeholderFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $form;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magedelight\Catalog\Model\TooltipFactory
     */
    protected $tooltipFactory;

    /**
     * @var string
     */
    protected $html_id_prefix;

    /**
     * @var string
     */
    protected $html_field_name_prefix;

    /**
     * @var string
     */
    protected $html_id_suffix;

    /**
     * @var string
     */
    protected $html_field_name_suffix;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_configurableType;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory
     */
    protected $_productRequestCollectionFactory;

    /**
     * @var ProductRequestStoreCollectionFactory
     */
    protected $_productRequestStoreCollectionFactory;

    /**
     * @var ProductRequestWebsiteCollectionFactory
     */
    protected $_productRequestWebsiteCollectionFactory;

    /**
     * @var \Magedelight\Catalog\Model\Source\WarrantyBy
     */
    protected $warrantyBy;

    /**
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magedelight\Catalog\Model\Source\Condition $productCondition
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magedelight\Catalog\Model\PlaceholderFactory $placeholderFactory
     * @param \Magento\Framework\Data\Form $form
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magedelight\Catalog\Model\TooltipFactory $tooltipFactory
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory $productRequestCollectionFactory
     * @param ProductRequestWebsiteCollectionFactory $productRequestWebsiteCollectionFactory
     * @param ProductRequestStoreCollectionFactory $productRequestStoreCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magedelight\Catalog\Model\Source\Condition $productCondition,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magedelight\Catalog\Model\PlaceholderFactory $placeholderFactory,
        \Magento\Framework\Data\Form $form,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magedelight\Catalog\Model\TooltipFactory $tooltipFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\Eav\Model\Config $eavConfig,
        \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory $productRequestCollectionFactory,
        ProductRequestWebsiteCollectionFactory $productRequestWebsiteCollectionFactory,
        ProductRequestStoreCollectionFactory $productRequestStoreCollectionFactory,
        \Magedelight\Catalog\Model\Source\WarrantyBy $warrantyBy,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->authSession = $authSession;
        $this->scopeConfig = $context->getScopeConfig();
        $this->directoryHelper = $directoryHelper;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_priceHelper = $priceHelper;
        $this->_productCondition = $productCondition;
        $this->_vendorProductFactory = $vendorProductFactory;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->redirect = $redirect;
        $this->categoryRepository = $categoryRepository;
        $this->placeholderFactory = $placeholderFactory;
        $this->form = $form;
        $this->mediaConfig = $mediaConfig;
        $this->jsonEncoder = $jsonEncoder;
        $this->tooltipFactory = $tooltipFactory;
        $this->_configurableType = $configurableType;
        $this->_eavConfig = $eavConfig;
        $this->_productRequestCollectionFactory = $productRequestCollectionFactory;
        $this->_productRequestWebsiteCollectionFactory = $productRequestWebsiteCollectionFactory;
        $this->_productRequestStoreCollectionFactory = $productRequestStoreCollectionFactory;
        $this->warrantyBy = $warrantyBy;
        parent::__construct($context, $data);
    }

    /**
     *
     * @return boolean|int
     */
    public function isRequestResubmitted()
    {
        return $this->getRequest()->getParam('id', false);
    }

    /**
     *
     * @return string
     */
    public function getAttributeValue($attributeCode)
    {
        if (!$this->isRequestResubmitted()) {
            return '';
        }

        return $this->getCurrentRequest()->getData($attributeCode);
    }

    /**
     *
     * @param $prefix
     * @return string
     */
    protected function setHtmlIdPrefix($prefix)
    {
        $this->html_id_prefix = $prefix;
        return $this;
    }

    /**
     *
     * @param $prefix
     * @return string
     */
    protected function setFieldNamePrefix($prefix)
    {
        $this->html_field_name_prefix = $prefix;
        return $this;
    }

    /**
     *
     * @return string
     */
    protected function getHtmlIdPrefix()
    {
        return $this->html_id_prefix;
    }

    /**
     *
     * @return string
     */
    protected function getFieldNamePrefix()
    {
        return $this->html_field_name_prefix;
    }

    /**
     *
     * @return string
     */
    protected function getFieldNameSuffix()
    {
        return $this->html_field_name_suffix;
    }

    /**
     *
     * @param $suffix
     * @return string
     */
    protected function setFieldNameSuffix($suffix)
    {
        $this->html_field_name_suffix = $suffix;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getEditPostActionUrl()
    {
        $queryParams = [
            'p' => $this->getRequest()->getParam('p', 1),
            'sfrm' => $this->getRequest()->getParam('sfrm', 'l'),
            'limit' => $this->getRequest()->getParam('limit', 10),
        ];
        return $this->getUrl('rbcatalog/product/editpost', ['_query' => $queryParams]);
    }

    /**
     *
     * @return boolean
     */
    public function isProductEditMode()
    {
        return ($this->getProduct()->getId()) ? true : false;
    }

    /**
     *
     * @return boolean
     */
    public function isProductHasVariants()
    {
        if ($this->getCurrentRequest()) {
            if ($this->getCurrentRequest()->getHasVariants()) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @return \Magento\User\Model\User|null
     */
    public function getVendor()
    {
        return $this->authSession->getUser();
    }

    /**
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     */
    public function getLoadedCategory()
    {
        return $this->coreRegistry->registry('vendor_current_category');
    }

    /**
     * category Attribute Set Id
     * @return int
     */
    public function getCategoryAttributeSetId()
    {
        if ($this->coreRegistry->registry('vendor_current_category')->getMdCategoryAttributeSetId()) {
            return $this->coreRegistry->registry('vendor_current_category')->getMdCategoryAttributeSetId();
        } else {
            //fetch attribute set from Category Model Obejct
            return $this->coreRegistry->registry('vendor_current_category')->getCategoryAttributeSetId();
        }
    }

    /**
     *
     * @return array
     */
    protected function getVitalAttributes()
    {
        if (empty($this->vitalAttributes)) {
            $this->getAttributes();
        }
        return $this->vitalAttributes;
    }

    /**
     *
     * @return array
     */
    protected function getAdditionalAttributes()
    {
        if (empty($this->additionalAttributes)) {
            $this->getAttributes();
        }
        return $this->additionalAttributes;
    }

    /**
     *
     * @return array
     */
    public function getVariantAttributes()
    {
        if (empty($this->variantAttributes)) {
            $this->getAttributes();
        }
        return $this->variantAttributes;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function setPlaceholderIfExists($attribute)
    {
        $placeholder = $this->placeholderFactory->create()->getCollection()
            ->addFieldToFilter('attribute_id', $attribute->getId())
            ->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId())
            ->getFirstItem();
        ($placeholder && $placeholder->getValue()) ? $attribute->setLabel($placeholder->getValue()) : '';

        return $attribute;
    }

    /**
     * Retrive excluded attribute list from store configuration.
     *
     * @return array
     */
    public function getExcludeAttributeList()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $collection = $this->scopeConfig->getValue(self::XML_PATH_EXCLUDE_ATTRIBUTES, $storeScope);
        $collection = explode(',', $collection);

        return $collection;
    }

    /**
     *
     * @return boolean true | false
     */
    public function checkVendorSkuValidation()
    {
        return $this->helper->checkVendorSkuValidation();
    }

    /**
     *
     * @return boolean true | false
     */
    public function checkManufacturerSkuValidation()
    {
        return $this->helper->checkManufacturerSkuValidation();
    }

    /**
     *
     * @return boolean true | false
     */
    public function isAllowVariants()
    {
        return $this->helper->isAllowVariants();
    }

    public function getFormateDate($date)
    {
        if ($date) {
            $date = date('m/d/Y', strtotime($date));
        } else {
            $date = null;
        }
        return $date;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTooltipText($attribute)
    {
        $tooltip = $this->tooltipFactory->create()->getCollection()
            ->addFieldToFilter('attribute_id', $attribute->getId())
            ->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId())
            ->getFirstItem();
        return ($tooltip && $tooltip->getValue()) ? $tooltip->getValue() : '';
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirect->getRefererUrl();
    }

    /**
     * @param string $status
     * @return string
     */
    public function getStatusTextForListing($status = '')
    {
        switch ($status) {
            case 0:
                return 'pending';
            case 2:
                return 'disapproved';
            default:
                return 'approve';
        }
    }
}
