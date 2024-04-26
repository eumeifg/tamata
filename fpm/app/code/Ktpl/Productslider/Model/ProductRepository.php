<?php
namespace Ktpl\Productslider\Model;

use Magento\Catalog\Model\ProductRepository\MediaGalleryProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Ktpl\ProductLabel\Helper\Data as DataHelper;
use Magento\ConfigurableProduct\Pricing\Price\FinalPriceResolver;

/**
 * Product Repository.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductRepository implements \Ktpl\Productslider\Api\ProductRepositoryInterface
{
    /**
     * @var Product[]
     */
    protected $instances = [];

    /**
     * @var Product[]
     */
    protected $instancesById = [];

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @deprecated 103.0.2
     *
     * @var \Magento\Catalog\Model\Product\Gallery\Processor
     */
    protected $mediaGalleryProcessor;

    /**
     * @var MediaGalleryProcessor
     */
    protected $mediaProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var int
     */
    protected $cacheLimit = 0;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var ProductsliderManagementNew
     */
    protected $productSliderManagementNew;

    /**
     * @var FinalPriceResolver
     */
    protected $priceResolver;

    /**
     * @var \Magedelight\Catalog\Model\Product
     */
    protected $vendorProduct;

    /**
     * @var \Magedelight\Catalog\Helper\Pricing\Data
     */
    protected $pricingHelper;
    protected $vipHelper;
    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    private $catalogHelper;

    /**
     * ProductRepository constructor.
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Gallery\ReadHandler $mediaGalleryProcessor
     * @param DataHelper $dataHelper
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param ProductsliderManagementNew $productSliderManagementNew
     * @param FinalPriceResolver $priceResolver
     * @param \Magedelight\Catalog\Model\Product $vendorProduct
     * @param \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param int $cacheLimit
     */
    public function __construct(
        \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Gallery\ReadHandler $mediaGalleryProcessor,
        DataHelper $dataHelper,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        ProductsliderManagementNew $productSliderManagementNew,
        FinalPriceResolver $priceResolver,
        \Magedelight\Catalog\Model\Product $vendorProduct,
        \Magedelight\Catalog\Helper\Pricing\Data $pricingHelper,
        \Magedelight\Catalog\Helper\Data $catalogHelper,
        CollectionProcessorInterface $collectionProcessor = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        \MDC\Catalog\Helper\OnlyXLeft $mdcHelper,
        \CAT\VIP\Helper\Data $vipHelper,
        $cacheLimit = 1000
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->cacheLimit = (int)$cacheLimit;
        $this->mediaGalleryProcessor = $mediaGalleryProcessor;
        $this->dataHelper = $dataHelper;
        $this->reviewFactory = $reviewFactory;
        $this->storeManager = $storeManager;
        $this->productSliderManagementNew = $productSliderManagementNew;
        $this->priceResolver = $priceResolver;
        $this->vendorProduct = $vendorProduct;
        $this->pricingHelper = $pricingHelper;
        $this->catalogHelper = $catalogHelper;
        $this->mdcHelper = $mdcHelper;
        $this->vipHelper = $vipHelper;
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }
        $serializeData = $this->serializer->serialize($serializeData);
        return sha1($serializeData);
    }

    /**
     * Add product to internal cache and truncate cache if it has more than cacheLimit elements.
     *
     * @param string $cacheKey
     * @param $product
     * @return void
     */
    private function cacheProduct($cacheKey, $product)
    {
        $this->instancesById[$product->getId()][$cacheKey] = $product;
        $this->saveProductInLocalCache($product, $cacheKey);

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, $offset, null, true);
            $this->instances = array_slice($this->instances, $offset, null, true);
        }
    }

    /**
     * @inheritdoc
     */
    public function getList($slider, $limit = null)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productSliderManagementNew->getProductsBySliderType($slider);
        $productCount = (!$limit) ? $slider->getLimitNumber() : $limit;
        $searchCriteria = $this->searchCriteriaBuilder->create()->setPageSize($productCount);
        $this->collectionProcessor->process($searchCriteria, $collection);
        $collection->load();
        $collection->addCategoryIds();
        foreach ($collection->getItems() as $entity){
            if($entity->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
                $price = $this->getPriceForConfigurable($entity);
                $finalPrice = $entity->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
                if($price > $finalPrice){
					$specialPrice = $finalPrice;
				}
				else {
					$specialPrice = $price;
				}
                $price = round($price);
                $specialPrice = round($specialPrice);
                $entity->setVendorPrice($price);
                $entity->setVendorSpecialPrice($specialPrice);
            }else{
                $defaultVendorProduct = $this->catalogHelper->getProductDefaultVendor($entity->getId());
                if($defaultVendorProduct && $defaultVendorProduct->getVendorProductId()){

                    $regularPrice = $entity->getPrice();
                    $entity->setPrice(round($regularPrice)); // changed catalog product price to round

                    $entity->setVendorId($defaultVendorProduct->getVendorId());
                    $defaultVendorPrice = round($defaultVendorProduct->getPrice());
                    $defaultVendorSpecialPrice = round($defaultVendorProduct->getSpecialPrice());
                    $entity->setVendorPrice($defaultVendorPrice);
                    $entity->setVendorSpecialPrice($defaultVendorSpecialPrice);
                }
            }

            if($entity->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
                $salableQty = "";
            }else{
                $thresoldStatus = $this->mdcHelper->getProductXleftById($entity->getId());

                if($thresoldStatus["status"]){

                        $salableQty = $thresoldStatus['qty'];
                    }else{
                        $salableQty = "";
                    }
            }

            $entity->setOnlyXLeft($salableQty);

            // Check Vip Price
            $price = $this->vipHelper->getvipProductDiscountPrice($entity->getID(),$entity->getVendorSpecialPrice(),$entity->getVendorId(),true);
            if($price){
                $entity->setVipPrice($price);
            }
            else{
                $entity->setVipPrice(0);
            }



            $this->mediaGalleryProcessor->execute($entity);
            $imageUrl = null;
            foreach ($entity->getMediaGalleryEntries() as $image)
            {
                $imageUrl = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ).'catalog/product'. $image->getFile();
                break;
            }
            $entity->setImageUrl($imageUrl);
            $entity->setProductLabels($this->dataHelper->getProductPLabels($entity));
            $this->processReviews($entity);
        }
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param $entity
     * @return float|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getPriceForConfigurable($entity){
        $productPrice = [];
        foreach ($entity->getTypeInstance()->getUsedProducts($entity) as $subProduct) {
            $productPrice[] = $this->pricingHelper->currency(
                $this->vendorProduct->getMinimumPriceForProduct($subProduct),
                false,
                false,
                true
            );
            if (!$productPrice) {
                $productPrice = $this->priceResolver->resolvePrice($subProduct);
            }
        }
        return min($productPrice);
    }

    protected function processReviews($entity)
    {
        $this->reviewFactory->create()->getEntitySummary(
            $entity,
            $this->storeManager->getStore()->getId()
        );
        $reviewCount = $entity->getRatingSummary()->getReviewsCount();
        $ratingSummary = $entity->getRatingSummary()->getRatingSummary();
        if ($reviewCount == null) {
            $reviewCount = 0;
        }
        if ($ratingSummary == null) {
            $ratingSummary = 0;
        }
        $entity->setRatingSummary($ratingSummary);
        $entity->setReviewCount($reviewCount);
        return $this;
    }

    /**
     * Clean internal product cache
     *
     * @return void
     */
    public function cleanCache()
    {
        $this->instances = null;
        $this->instancesById = null;
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 102.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Catalog\Model\Api\SearchCriteria\ProductCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }

    /**
     * Saves product in the local cache.
     *
     * @param Product $product
     * @param string $cacheKey
     * @return void
     */
    private function saveProductInLocalCache($product, string $cacheKey) : void
    {
        $preparedSku = $this->prepareSku($product->getSku());
        $this->instances[$preparedSku][$cacheKey] = $product;
    }

    /**
     * Converts SKU to lower case and trims.
     *
     * @param string $sku
     * @return string
     */
    private function prepareSku(string $sku): string
    {
        return mb_strtolower(trim($sku));
    }

}
