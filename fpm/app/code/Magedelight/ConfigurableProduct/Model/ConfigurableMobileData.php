<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ConfigurableProduct
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ConfigurableProduct\Model;

use Magedelight\ConfigurableProduct\Api\Data\ConfigurableAttributeDataInterfaceFactory;
use Magedelight\ConfigurableProduct\Api\Data\ConfigurableDataInterfaceFactory;
use Magedelight\ConfigurableProduct\Api\Data\ConfigurableOptionDataInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch as SwatchModel;

class ConfigurableMobileData extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productloader;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProduct;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magedelight\Catalog\Model\Product
     */
    protected $vendorProductFactory;

    /**
     * @var SwatchData
     */
    protected $swatchHelper;

    /**
     * @var Media
     */
    protected $swatchMediaHelper;

    /**
     * @var ConfigurableDataInterfaceFactory
     */
    protected $configurableData;

    /**
     * @var ConfigurableAttributeDataInterfaceFactory
     */
    protected $configurableAttributeData;

    /**
     * @var ConfigurableOptionDataInterfaceFactory
     */
    protected $configurableOptionData;

    /**
     * @var AssociativeArrayItemFactory
     */
    protected $associativeArrayItem;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magedelight\Authorization\Model\ResourceModel\Role $resource
     * @param \Magedelight\Authorization\Model\ResourceModel\Role\Collection $resourceCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $_productloader
     * @param \Magento\ConfigurableProduct\Helper\Data $helper
     * @param \Magedelight\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param ConfigurableDataInterfaceFactory $configurableData
     * @param ConfigurableAttributeDataInterfaceFactory $configurableAttributeData
     * @param ConfigurableOptionDataInterfaceFactory $configurableOptionData
     * @param AssociativeArrayItemFactory $associativeArrayItem
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Authorization\Model\ResourceModel\Role $resource,
        \Magedelight\Authorization\Model\ResourceModel\Role\Collection $resourceCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\ConfigurableProduct\Helper\Data $helper,
        \Magedelight\Catalog\Helper\Data $catalogHelper,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        ConfigurableDataInterfaceFactory $configurableData,
        ConfigurableAttributeDataInterfaceFactory $configurableAttributeData,
        ConfigurableOptionDataInterfaceFactory $configurableOptionData,
        AssociativeArrayItemFactory $associativeArrayItem,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_productloader = $_productloader;
        $this->helper = $helper;
        $this->catalogHelper = $catalogHelper;
        $this->catalogProduct = $catalogProduct;
        $this->stockRegistry = $stockRegistry;
        $this->vendorProductFactory = $vendorProductFactory->create();
        $this->swatchHelper = $swatchHelper;
        $this->swatchMediaHelper = $swatchMediaHelper;
        $this->configurableData = $configurableData;
        $this->configurableAttributeData = $configurableAttributeData;
        $this->configurableOptionData = $configurableOptionData;
        $this->associativeArrayItem = $associativeArrayItem;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare Data for Configurable Product (for Mobile Only)
     *
     * @param $entity
     * @return \Magedelight\ConfigurableProduct\Api\Data\ConfigurableDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfigurableData($entity)
    {
        $store = $this->_storeManager->getStore();
        $currentProduct = $this->_productloader->create()->load($entity->getId());
        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');

        $options = $this->getOptions($currentProduct, $this->getAllowProducts($currentProduct));
        $stockData = $this->getIsInStockStatus();
        $attributesData = $this->getAttributesData($currentProduct, $options);
        $indexData = isset($options['index']) ? $options['index'] : [];
        $mobileAttributeOptions = $this->getMobileAttributesData($currentProduct, $options);
        $defaultValues = [];
        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $defaultValues = $attributesData['defaultValues'];
        }
        $configurableData = $this->configurableData->create();
        $configurableData->setAttributes($attributesData['attributes'])
                         ->setDefaultValues($defaultValues)
                         ->setInStockIds($stockData)
                         ->setIndex([$indexData])
                         ->setMobileAttributes($mobileAttributeOptions['attributes']);

        return $configurableData;
    }

    /**
     * Get product attributes
     *
     * @param Product $product
     * @param array $options
     * @return array
     */
    public function getAttributesData(Product $product, array $options = [])
    {
        $defaultValues = [];
        $attributes = [];
        foreach ($product->getTypeInstance()->getConfigurableAttributes($product) as $attribute) {
            $attributeOptionsData = $this->getAttributeOptionsData($attribute, $options);
            if ($attributeOptionsData) {
                $productAttribute = $attribute->getProductAttribute();
                $attributeId = $productAttribute->getId();
                $configurableAttrData = $this->configurableAttributeData->create();
                $frontendInput = $productAttribute->getFrontendInput();
                if ($this->swatchHelper->isSwatchAttribute($productAttribute)) {
                    if ($this->swatchHelper->isVisualSwatch($productAttribute)) {
                        $frontendInput = SwatchModel::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT;
                    } elseif ($this->swatchHelper->isTextSwatch($productAttribute)) {
                        $frontendInput = SwatchModel::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT;
                    }
                }
                $configurableAttrData->setId($attributeId)
                                     ->setCode($productAttribute->getAttributeCode())
                                     ->setFrontendInput($frontendInput)
                                     ->setLabel($productAttribute->getStoreLabel($product->getStoreId()))
                                     ->setOptions($attributeOptionsData);

                $attributes[$attribute->getPosition()] = $configurableAttrData;

                $defaultValues[$attributeId] = $this->getAttributeConfigValue($attributeId, $product);
            }
        }
        return [
            'attributes' => $attributes,
            'defaultValues' => $defaultValues,
        ];
    }

    /**
     * @param int $attributeId
     * @param Product $product
     * @return mixed|null
     */
    protected function getAttributeConfigValue($attributeId, $product)
    {
        return $product->hasPreconfiguredValues()
            ? $product->getPreconfiguredValues()->getData('super_attribute/' . $attributeId)
            : null;
    }

    /**
     * @param Attribute $attribute
     * @param array $config
     * @return array
     */
    protected function getAttributeOptionsData($attribute, $config)
    {
        $attributeOptionsData = [];
        foreach ($attribute->getOptions() as $attributeOption) {
            $optionId = $attributeOption['value_index'];
            $childId = isset($config[$attribute->getAttributeId()][$optionId][0])
                    ? $config[$attribute->getAttributeId()][$optionId][0]
                    : null;
            $swatchDataArray = $this->swatchHelper->getSwatchesByOptionsId([$optionId]);
            $swatchData = null;
            if (!empty($swatchDataArray)) {
                if ($swatchDataArray[$optionId]['type'] == SwatchModel::SWATCH_TYPE_VISUAL_IMAGE) {
                    $swatchData = $this->swatchMediaHelper->getSwatchAttributeImage(
                        SwatchModel::SWATCH_THUMBNAIL_NAME,
                        $swatchDataArray[$optionId]['value']
                    );
                } else {
                    $swatchData = $swatchDataArray[$optionId]['value'];
                }
            }
            $optionsData = $this->configurableOptionData->create();
            $optionsData->setId($optionId)
                        ->setLabel($attributeOption['label'])
                        ->setSwatchValue($swatchData);
            $attributeOptionsData[] = $optionsData->getData();
        }

        return $attributeOptionsData;
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param \Magento\Catalog\Model\Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $allowAttributes = $this->getAllowAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            $associative = $this->associativeArrayItem->create();
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                $options[$productAttributeId][$attributeValue] = $productId;
                /*$data[$productId][$productAttributeId] = $associative->setKey($productAttributeId)
                    ->setValue($attributeValue);*/
                $options['index'][$productId][$productAttributeId] = $attributeValue;
            }
        }

        return $options;
    }

    /**
     * Get allowed attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAllowAttributes($product)
    {
        return $product->getTypeInstance()->getConfigurableAttributes($product);
    }

    protected function getIsInStockStatus()
    {
        return $this->getData('in_stock_products');
    }

    /**
     * Get Allowed Products
    * @return \Magento\Catalog\Model\Product[]
     */
    public function getAllowProducts($product)
    {
        $products = [];
        $productIds = [];
        $skipSaleableCheck = $this->catalogProduct->getSkipSaleableCheck();
        $allProducts = $product->getTypeInstance()->getUsedProducts($product, null);
        foreach ($allProducts as $product) {
            if ($product->isSaleable() || $skipSaleableCheck) {
                if ($this->vendorProductFactory->checkIsProductSalableFromVendor($product->getId())) {
                    $products[] = $product;
                    $productStock = $this->stockRegistry->getStockItem($product->getId());
                    if ($productStock->getIsInStock()) {
                        $productIds[] = $product->getId();
                    }
                }
            }
        }
        $this->setData('in_stock_products', $productIds);
        $this->setAllowProducts($products);

        return $this->getData('allow_products');
    }

     /**
     * @param Attribute $attribute
     * @param array $config
     * @return array
     */
    protected function getMobileAttributeOptionsData($attribute, $config)
    {
        $attributeOptionsData = [];
        if($attribute->getAttributeId() && $attribute->getAttributeId()!= '')
        {
            $attributeId =  $attribute->getAttributeId();
            if(!empty($config))
            {
               foreach ($config[$attributeId] as $key => $value)
               {
                     $configData[] = $key; 
                }  
            }            
        }

        foreach ($attribute->getOptions() as $attributeOption) {
            $optionId = $attributeOption['value_index'];
            $childId = isset($config[$attribute->getAttributeId()][$optionId][0])
                    ? $config[$attribute->getAttributeId()][$optionId][0]
                    : null;
            $swatchDataArray = $this->swatchHelper->getSwatchesByOptionsId([$optionId]);
            $swatchData = null;
            if (!empty($swatchDataArray)) {
                if ($swatchDataArray[$optionId]['type'] == SwatchModel::SWATCH_TYPE_VISUAL_IMAGE) {
                    $swatchData = $this->swatchMediaHelper->getSwatchAttributeImage(
                        SwatchModel::SWATCH_THUMBNAIL_NAME,
                        $swatchDataArray[$optionId]['value']
                    );
                } else {
                    $swatchData = $swatchDataArray[$optionId]['value'];
                }
            }
            $optionsData = $this->configurableOptionData->create();
            if(!empty($configData)){

                if(in_array($optionId, $configData))
                {
                  $optionsData->setId($optionId)
                            ->setLabel($attributeOption['label'])
                            ->setSwatchValue($swatchData);
                   $attributeOptionsData[] = $optionsData->getData();  
                }
            }else{                
                $optionsData->setId($optionId)
                        ->setLabel($attributeOption['label'])
                        ->setSwatchValue($swatchData);
                $attributeOptionsData[] = $optionsData->getData();    
            }      
        }
        return $attributeOptionsData;
    }

    public function getMobileAttributesData(Product $product, array $options = [])
    {
        $defaultValues = [];
        $attributes = [];
        foreach ($product->getTypeInstance()->getConfigurableAttributes($product) as $attribute) {
            $attributeOptionsData = $this->getMobileAttributeOptionsData($attribute, $options);
            if ($attributeOptionsData) {
                $productAttribute = $attribute->getProductAttribute();
                $attributeId = $productAttribute->getId();
                $configurableAttrData = $this->configurableAttributeData->create();
                $frontendInput = $productAttribute->getFrontendInput();
                if ($this->swatchHelper->isSwatchAttribute($productAttribute)) {
                    if ($this->swatchHelper->isVisualSwatch($productAttribute)) {
                        $frontendInput = SwatchModel::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT;
                    } elseif ($this->swatchHelper->isTextSwatch($productAttribute)) {
                        $frontendInput = SwatchModel::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT;
                    }
                }
                $configurableAttrData->setId($attributeId)
                                     ->setCode($productAttribute->getAttributeCode())
                                     ->setFrontendInput($frontendInput)
                                     ->setLabel($productAttribute->getStoreLabel($product->getStoreId()))
                                     ->setOptions($attributeOptionsData);

                $attributes[$attribute->getPosition()] = $configurableAttrData;

                $defaultValues[$attributeId] = $this->getAttributeConfigValue($attributeId, $product);
            }
        }
        return [
            'attributes' => $attributes,
            'defaultValues' => $defaultValues,
        ];
    }
}
