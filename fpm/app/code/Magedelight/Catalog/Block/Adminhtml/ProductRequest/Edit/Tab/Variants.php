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
namespace Magedelight\Catalog\Block\Adminhtml\ProductRequest\Edit\Tab;

use Magedelight\Catalog\Model\ProductRequest;
use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Locale\CurrencyInterface;

class Variants extends GenericForm implements TabInterface
{

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;

    /**
     * @var CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\Json\Encoder
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Json\Decoder
     */
    protected $_jsonDecoder;

    protected $_productRequestModel;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory
     */
    protected $_productRequestCollectionFactory;

    /**
     * @var \Magedelight\Catalog\Model\Source\Condition
     */
    protected $productCondition;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Json\Encoder $jsonEncoder
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param LocatorInterface $locator
     * @param CurrencyInterface $localeCurrency
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory $productRequestCollectionFactory
     * @param \Magedelight\Catalog\Model\Source\Condition $productCondition
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        LocatorInterface $locator,
        CurrencyInterface $localeCurrency,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory $productRequestCollectionFactory,
        \Magedelight\Catalog\Model\Source\Condition $productCondition,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_jsonDecoder = $jsonDecoder;
        $this->_eavConfig = $eavConfig;
        $this->_priceHelper = $priceHelper;
        $this->locator = $locator;
        $this->localeCurrency = $localeCurrency;
        $this->helper = $helper;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_productRequestCollectionFactory = $productRequestCollectionFactory;
        $this->productCondition = $productCondition;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     *
     */
    public function _construct()
    {
        $this->setTemplate('Magedelight_Catalog::productrequest/edit/tab/variants.phtml');
        $this->setCurrentStore();
        parent::_construct();
    }

    /**
     * Prepare form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var $model ProductRequest */
        $this->_productRequestModel = $model = $this->_coreRegistry->registry('vendor_product_request');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('productrequest_');

        $isCollapsable = true;
        $group = $this->getGroup();
        $legend = __(ucwords(str_replace('_', ' ', $group)));

        // Initialize product object as form property to use it during elements generation
        $form->setDataObject($model);

        $fieldset = $form->addFieldset(
            'group-fields-' . $group,
            ['class' => 'user-defined', 'legend' => $legend, 'collapsable' => $isCollapsable]
        );

        $fieldset->addField('variants', 'hidden', ['name' => 'variants']);

        $form->setFieldNameSuffix('product');
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     *
     * @return array|void data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariantsData()
    {
        $model = $this->_coreRegistry->registry('vendor_product_request');

        $collection = $this->_productRequestCollectionFactory->create();
        $collection->getSelect()->join(
            ['mvprw' => 'md_vendor_product_request_website'],
            'mvprw.product_request_id = main_table.product_request_id AND mvprw.website_id = '
            . $this->_storeManager->getStore()->getWebsiteId(),
            ['*']
        );
        $collection->getSelect()->join(
            ['mvprs' => 'md_vendor_product_request_store'],
            'mvprs.product_request_id = main_table.product_request_id AND mvprs.store_id = '
            . $this->_storeManager->getStore()->getId(),
            ['*']
        );
        $collection->getSelect()->join(
            ['mvprsl' => 'md_vendor_product_request_super_link'],
            'mvprsl.product_request_id = main_table.product_request_id AND mvprsl.parent_id = '
            . $model->getProductRequestId(),
            ['parent_id']
        );
        if ($collection && $collection->getSize() > 0) {
            $finalData = [];
            foreach ($collection as $variant) {
                $options = $this->_jsonDecoder->decode($variant->getAttributes());
                $variantData = $variant->getData();
                foreach ($options as $index => $value) {
                    $variantData[$index] = $options[$index];
                }
                $finalData[] = $variantData;
            }
            return $finalData;
        }
        return $this;
    }

    /**
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEncodedVariantsData()
    {
        return $this->_jsonEncoder->encode($this->getVariantsData());
    }

    /**
     * @return array|mixed
     */
    public function getVariantColumns()
    {
        $columns = $this->helper->getVariantColumns();
        $attributeCodes = $this->getUsedProductAttributeIds();
        $res = array_slice($columns, 0, 1, true) +
            $attributeCodes + array_slice($columns, 1, count($columns) - 1, true);
        return $res;
    }

    /**
     * @return mixed
     */
    public function getUsedProductAttributeIds()
    {
        $usedAttributeIds = $this->_productRequestModel->getUsedProductAttributeIds();
        if ($usedAttributeIds) {
            return $this->_jsonDecoder->decode($usedAttributeIds);
        }
    }

    /**
     * replace space with underscore in string
     * @param type $string
     * @return string
     */
    public function getFieldName($string = null)
    {

        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "_", $string);

        return $string;
    }

    /**
     *
     * @param $field - attribute code
     * @param $value - attribute value
     * @return string|int User value
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function renderField($field, $value)
    {
        $attributeCodes = $this->getUsedProductAttributeIds();

        if ($field == 'price' || $field == 'special_price') {
            $value = ($value) ? $this->_priceHelper->currency($value) : '';
        } elseif (false !== array_search($field, $attributeCodes)) {
            $attribute = $this->_eavConfig->getAttribute('catalog_product', $field);
            if (is_array($value)) {
                $value = $value[0];
            }
            $value = $attribute->getSource()->getOptionText($value);
        } elseif ($field == 'condition') {
            $value = $this->productCondition->getOptionText($value);
        } elseif ($field == 'warranty_type') {
            $value = ($value == 1) ? 'Manufacturer' : 'Vendor';
        } elseif ($field === 'image') {
            $tempValue = $value;
            if ($this->helper->getTmpFileIfExists($tempValue)) {
                $value = '<img class="child_image" data-role="image-element" src="' . $this->mediaConfig->getTmpMediaUrl($tempValue) . '" alt="' . $tempValue . '" />';
            } else {
                $value = $value;
            }
        } else {
            $value = $value;
        }

        return $value;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Product Variants');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Product Variants');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @return bool
     */
    public function getProductRequestData()
    {
        if (!($this->_productRequestModel === null)) {
            return $this->_productRequestModel;
        }
        return false;
    }

    /**
     *
     * @param $price
     * @return string
     * @throws \Zend_Currency_Exception
     */
    public function getPriceString($price)
    {
        $currency = $this->localeCurrency->getCurrency($this->locator->getBaseCurrencyCode());
        return $currency->toCurrency(sprintf("%f", $price));
    }

    /**
     * @return mixed
     */
    public function getCurrencySymbol()
    {
        return $this->locator->getStore()->getBaseCurrency()->getCurrencySymbol();
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     * @deprecated
     */
    private function getStoreManager()
    {
        if (null === $this->storeManager) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class);
        }
        return $this->storeManager;
    }

    /**
     * @return \Magento\Store\Model\StoreFactory
     */
    private function getStoreFactory()
    {
        if (null === $this->storeFactory) {
            $this->storeFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreFactory::class);
        }
        return $this->storeFactory;
    }

    /**
     *
     */
    protected function setCurrentStore()
    {
        $store = $this->getStoreFactory()->create();
        $store->load(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        if (!$this->_coreRegistry->registry('current_store')) {
            $this->_coreRegistry->register('current_store', $store);
        }
    }

    /**
     * Get variation key
     *
     * @param array $options
     * @return string
     */
    protected function getVariationKey(array $options = [])
    {
        $result = [];

        foreach ($options as $option) {
            $result[] = $option['value'];
        }

        asort($result);

        return implode('-', $result);
    }
}
