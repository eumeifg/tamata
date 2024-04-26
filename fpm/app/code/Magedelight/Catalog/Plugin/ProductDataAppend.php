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
namespace Magedelight\Catalog\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\ScopeInterface;

class ProductDataAppend
{
    protected $priceResolver;

    /**
     * @var Magedelight\Catalog\Model\Product
     */
    private $vendorProduct;

    /**
     * @var LowestPriceOptionsProviderInterface
     */
    private $lowestPriceOptionsProvider;

    protected $request;

    /**
     * @var ProductExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var \Magedelight\Vendor\Helper\Microsite\Data
     */
    protected $_micrositeHelper;

    protected $mainCollection = null;

    /**
     * ProductDataAppend constructor.
     * @param \Magedelight\Catalog\Model\Product $vendorProduct
     * @param \Magedelight\Catalog\Api\ProductRepositoryInterface $mdProductRepositoryInterface
     * @param \Magedelight\Catalog\Api\Data\ProductDataInterface $mdProductDataInterface
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magedelight\Catalog\Api\Data\ProductAdditionalAttributeDataInterface $mdProductAdditionalData
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistCollectionFactory
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param ReviewFactory $reviewFactory
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory
     * @param ProductExtensionFactory $extensionFactory
     * @param \Magedelight\Catalog\Helper\Data $catalogHelper
     * @param \Magedelight\Vendor\Helper\Microsite\Data $micrositeHelper
     */
    public function __construct(
        \Magedelight\Catalog\Model\Product $vendorProduct,
        \Magedelight\Catalog\Api\ProductRepositoryInterface $mdProductRepositoryInterface,
        \Magedelight\Catalog\Api\Data\ProductDataInterface $mdProductDataInterface,
        PriceCurrencyInterface $priceCurrency,
        \Magedelight\Catalog\Api\Data\ProductAdditionalAttributeDataInterface $mdProductAdditionalData,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistCollectionFactory,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        ReviewFactory $reviewFactory,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory,
        ProductExtensionFactory $extensionFactory,
        \Magedelight\Catalog\Helper\Data $catalogHelper,
        \Magedelight\Vendor\Helper\Microsite\Data $micrositeHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $getSalableQuantityDataBySku
    ) {
        $this->vendorProduct = $vendorProduct;
        $this->mdProductRepositoryInterface = $mdProductRepositoryInterface;
        $this->mdProductDataInterface = $mdProductDataInterface;
        $this->priceCurrency = $priceCurrency;
        $this->mdProductAdditionalData = $mdProductAdditionalData;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->userContext = $userContext;
        $this->_reviewFactory = $reviewFactory;
        $this->_request = $request;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->extensionFactory = $extensionFactory;
        $this->catalogHelper = $catalogHelper;
        $this->_micrositeHelper = $micrositeHelper;
        $this->scopeConfig = $scopeConfig;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
    }

    /**
     * @param ProductInterface $entity
     * @param ProductExtensionInterface|null $extension
     * @return \Magento\Catalog\Api\Data\ProductExtension|ProductExtensionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetExtensionAttributes(
        ProductInterface $entity,
        ProductExtensionInterface $extension = null
    ) {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }

        switch ($entity->getTypeId()) {
            case Configurable::TYPE_CODE:
                $defaultVendorProduct = $this->catalogHelper->getVendorDefaultProduct($entity->getId(), false, false, true);
                $entityId = ($defaultVendorProduct) ?
                    $defaultVendorProduct->getMarketplaceProductId() :
                    $entity->getId();
                break;

            case ProductType::TYPE_SIMPLE:
                $entityId = $entity->getId();
                break;

            default:
                $entityId = $entity->getId();
                break;
        }

        $collection = $this->mdProductRepositoryInterface->getById($entityId);
        $this->processVendorCollection($collection);
        $this->processAdditionalInformation($entity, $entity->getAttributes());
        $this->processMediaUrls();
        $this->processWishlistFlag($entity->getId());
        $this->processReviews($entity);
        $extension->setAppendedData($this->mdProductDataInterface);
        $entity->setExtensionAttributes($extension);
        return $extension;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function processMediaUrls()
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $productImagePath = $mediaUrl . 'catalog/product';
        $vendorLogoPath = $mediaUrl . 'vendor/logo';
        $this->mdProductDataInterface->setMediaPath($productImagePath);
        $this->mdProductDataInterface->setVendorLogoPath($vendorLogoPath);
        return $this;
    }

    protected function processVendorCollection($collection)
    {
        try {
            $vId = ($this->_request->getParam('v', false));

            /*Get qty threshold for only x left item*/
            $thresholdQty = $this->scopeConfig->getValue("cataloginventory/options/stock_threshold_qty",ScopeInterface::SCOPE_STORE);            
            /*Get qty threshold for only x left item*/

            $this->vendorProduct->_addVendorData($collection);
            $this->vendorProduct->_addRbVendorProductWebsiteData($collection);
            $this->vendorProduct->_addRbVendorProductStoreData($collection);
            $this->vendorProduct->_addStoreIds($collection);
            $this->vendorProduct->_addWebsiteIds($collection);
            $this->vendorProduct->addProductData($collection);
            $collection->addFilterToMap('product_name', 'cpev.value');
            $collection->addFilterToMap('vendor_id', 'main_table.vendor_id');
            $collection->addFilterToMap('store_id', 'rbvps.store_id');
            $collection->getSelect()->group('main_table.vendor_product_id');
            $collection->processCollectionForFrontend($collection, true);
            $vendorCollectionSize =  $collection->getSize();

            
            if($collection->getFirstItem()->getVendorId()){
                /* Set this flag for default vendor only */
                if($this->_micrositeHelper->getVendorMicrositeUrl(
                    $collection->getFirstItem()->getVendorId())){
                    $vendorMicroSiteStatus = true;
                }else{
                    $vendorMicroSiteStatus = false;
                }
                $collection->getFirstItem()->setData('show_microsite',$vendorMicroSiteStatus);

                $salableQuantityDataBySku = $this->getSalableQuantityDataBySku->execute($collection->getFirstItem()->getData('sku'));

                $salableQty = $salableQuantityDataBySku[0]['qty'];

                $onlyXLeft = ($salableQty <= $thresholdQty && $salableQty > 0 ) ? $salableQty : "";
                $collection->getFirstItem()->setData('only_x_left', (string)$onlyXLeft);
            }
            
            if ($vId) {
                if ($vendorCollectionSize > 1) {
                    foreach ($collection as $vendorProduct)
                    {
                        if($vendorProduct->getVendorId() === $vId){
                            $defaultVendorData[] = $vendorProduct->getData();
                        }
                    }
                } else {
                    $defaultVendorData[] = $collection->getFirstItem()->getData();
                }
            } else {
                $defaultVendorData[] = $collection->getFirstItem()->getData();
            }

            $this->mdProductDataInterface->setDefaultVendorData($defaultVendorData);

            if ($vendorCollectionSize > 1) {
                $otherVendorCollection = clone $collection;
                $otherVendorData = $otherVendorCollection->getData();

                if ($vId) {
                    $removeVendorId = $vId;
                } else {
                    $removeVendorId = $collection->getFirstItem()->getVendorId();
                }

                foreach ($otherVendorData as $key => $otherVendor) {
                    if ($otherVendor['vendor_id'] == $removeVendorId) {
                        unset($otherVendorData[$key]);
                    }
                }
                $this->mdProductDataInterface->setOtherVendorData($otherVendorData);
            }
        } catch (\Exception $e) {
            $this->logger->critical('Error message', ['exception' => $e]);
        }

        return $this;
    }

    /**
     * @param $entity
     * @param $attributes
     * @return $this
     */
    protected function processAdditionalInformation($entity, $attributes)
    {
        $data = [];
        $attributes = $attributes;
        $attributeCounter = 0;
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront()) {
                $value = $attribute->getFrontend()->getValue($entity);

                if ($value instanceof Phrase) {
                    $value = (string)$value;
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }

                if (is_string($value) && strlen(trim($value))) {
                    $this->mdProductAdditionalData->setLabel($attribute->getStoreLabel());
                    $this->mdProductAdditionalData->setValue($value);
                    $this->mdProductAdditionalData->setCode($attribute->getAttributeCode());
                    $data[$attributeCounter] = $this->mdProductAdditionalData->getData();

                    $attributeCounter++;
                }
            }
        }
        $this->mdProductDataInterface->setAdditionalInformation($data);
        return $this;
    }

    /**
     * @param $productId
     */
    protected function processWishlistFlag($productId)
    {
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            $collection = $this->wishlistCollectionFactory->create()
                ->addCustomerIdFilter($customerId)
                ->addFieldToFilter('product_id', $productId);

            if ($collection->getSize() > 0) {
                $this->mdProductDataInterface->setWishlistFlag(true);
            } else {
                $this->mdProductDataInterface->setWishlistFlag(false);
            }
        } else {
            $this->mdProductDataInterface->setWishlistFlag(false);
        }
    }

    /**
     * @param $entity
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function processReviews($entity)
    {
        $this->_reviewFactory->create()->getEntitySummary($entity, $this->storeManager->getStore()->getId());
        $reviewCount = $entity->getRatingSummary()->getReviewsCount();
        $ratingSummary = $entity->getRatingSummary()->getRatingSummary();
        if ($reviewCount == null) {
            $reviewCount = 0;
        }
        if ($ratingSummary == null) {
            $ratingSummary = 0;
        }
        $this->mdProductDataInterface->setRatingSummary($ratingSummary);
        $this->mdProductDataInterface->setReviewCount($reviewCount);
        return $this;
    }
}
