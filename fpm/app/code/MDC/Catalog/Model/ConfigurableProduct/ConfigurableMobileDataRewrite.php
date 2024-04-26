<?php

namespace MDC\Catalog\Model\ConfigurableProduct;

use Magedelight\ConfigurableProduct\Api\Data\ConfigurableAttributeDataInterfaceFactory;
use Magedelight\ConfigurableProduct\Api\Data\ConfigurableDataInterfaceFactory;
use Magedelight\ConfigurableProduct\Api\Data\ConfigurableOptionDataInterfaceFactory;
use Magedelight\ConfigurableProduct\Model\AssociativeArrayItemFactory;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
/*use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;*/

class ConfigurableMobileDataRewrite extends \Magedelight\ConfigurableProduct\Model\ConfigurableMobileData
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
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /*protected $discount;*/

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
     * @param \Magento\Catalog\Helper\Image $imageHelper
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
        \Magento\Catalog\Helper\Image $imageHelper,
        /*Discount $discount,*/
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $storeManager, $_productloader, $helper, $catalogHelper, $catalogProduct, $stockRegistry, $vendorProductFactory, $swatchHelper, $swatchMediaHelper, $configurableData, $configurableAttributeData, $configurableOptionData, $associativeArrayItem, $data);
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
        $this->imageHelper = $imageHelper;
        /*$this->discount = $discount;*/
    }

    /**
     * @param $currentProduct
     * @param $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $allowAttributes = $this->getAllowAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            /*$_product = $this->vendorProductFactory->load($productId, 'marketplace_product_id');*/
            $_productCollection = $this->vendorProductFactory->getCollection();
            $_productCollection->addFieldToFilter('marketplace_product_id', ['eq' => $productId]);
            $_productCollection->addFieldToFilter('rbvpw.status', ['eq' => 1]);
            $price = $specialPrice = [];
            foreach ($_productCollection as $_product) {
                $price[] = $_product->getPrice();
                $specialPrice[] = $_product->getFinalPrice();
            }
            $price = min($price);
            $specialPrice = min($specialPrice);
            //$associative = $this->associativeArrayItem->create();
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                $options[$productAttributeId][$attributeValue] = $productId;
                /*$data[$productId][$productAttributeId] = $associative->setKey($productAttributeId)
                    ->setValue($attributeValue);*/
                $options['index'][$productId][$productAttributeId] = $attributeValue;
            }
            $options['index'][$productId]['image'] = $product->getData('image');
            $options['index'][$productId]['small_image'] = $this->imageHelper->init($product, 'small_image', ['type'=>'small_image'])->keepAspectRatio(true)->resize('195','195')->getUrl();
            $options['index'][$productId]['price'] = $price;//$product->getData('price');
            $options['index'][$productId]['special_price'] = $specialPrice;//(float) $product->getData('special_price') == 0 ? $product->getData('price') : $product->getData('special_price');
            /*if ((float)$product->getData('special_price') > 0) {
                $options['index'][$productId]['discount'] = $this->discount->getDiscountByDifference((float) $product->getData('price'), (float) $product->getData('special_price'));
            }*/
        }
        return $options;
    }
}
