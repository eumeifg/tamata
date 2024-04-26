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

namespace Magedelight\Vendor\Model\Microsite\Build;

use Magedelight\Vendor\Model\Microsite\VendorProducts;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magedelight\Vendor\Api\Microsite\ProductsInterface;
use Magedelight\Vendor\Api\Data\Microsite\FilterAndSortingDataInterfaceFactory;
use Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Build microsite products data
 */
class Products extends \Magento\Framework\DataObject implements ProductsInterface
{

    /**
     * @var VendorProducts
     */
    protected $vendorProducts;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\ReadHandler
     */
    protected $mediaGalleryProcessor;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product
     */
    protected $vendorProductResource;

    /**
     * @var ReadExtensions
     */
    protected $readExtensions;

    /**
     * @var \Magedelight\Vendor\Api\Data\Microsite\ProductInterfaceFactory
     */
    protected $productInterfaceFactory;

    /**
     * @var \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory
     */
    protected $wishlistCollectionFactory;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var FilterAndSortingDataInterfaceFactory
     */
    protected $filterAndSortingDataInterfaceFactory;

    /**
     * @var \Magedelight\Catalog\Model\Product
     */
    protected $vendorProductFactory;

    /**
     * @var VendorCollectionFactory
     */
    protected $vendorCollectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @param VendorProducts $vendorProducts
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magedelight\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Catalog\Model\Product\Gallery\ReadHandler $mediaGalleryProcessor
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource
     * @param ReadExtensions $readExtensions
     * @param \Magedelight\Vendor\Api\Data\Microsite\ProductInterfaceFactory $productInterfaceFactory
     * @param \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistCollectionFactory
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param FilterAndSortingDataInterfaceFactory $filterAndSortingDataInterfaceFactory
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param VendorCollectionFactory $vendorCollectionFactory
     * @param CollectionFactory $categoryCollectionFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param array $data
     */

    public function __construct(
        VendorProducts                                                 $vendorProducts,
        \Magento\Framework\Api\SearchCriteriaBuilder                   $searchCriteriaBuilder,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magedelight\Catalog\Helper\Data                               $catalogHelper,
        \Magento\Catalog\Model\Product\Gallery\ReadHandler             $mediaGalleryProcessor,
        \Magento\Review\Model\ReviewFactory                            $reviewFactory,
        \Magento\Store\Model\StoreManagerInterface                     $storeManager,
        \Magedelight\Catalog\Model\ResourceModel\Product               $vendorProductResource,
        ReadExtensions                                                 $readExtensions,
        \Magedelight\Vendor\Api\Data\Microsite\ProductInterfaceFactory $productInterfaceFactory,
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory   $wishlistCollectionFactory,
        \Magento\Authorization\Model\UserContextInterface              $userContext,
        FilterAndSortingDataInterfaceFactory                           $filterAndSortingDataInterfaceFactory,
        \Magedelight\Catalog\Model\ProductFactory                      $vendorProductFactory,
        VendorCollectionFactory                                        $vendorCollectionFactory,
        CollectionFactory                                              $categoryCollectionFactory,
        CollectionProcessorInterface                                   $collectionProcessor = null,
        array                                                          $data = []
    )
    {
        $this->vendorProducts = $vendorProducts;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->catalogHelper = $catalogHelper;
        $this->mediaGalleryProcessor = $mediaGalleryProcessor;
        $this->reviewFactory = $reviewFactory;
        $this->storeManager = $storeManager;
        $this->vendorProductResource = $vendorProductResource;
        $this->readExtensions = $readExtensions;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->userContext = $userContext;
        $this->filterAndSortingDataInterfaceFactory = $filterAndSortingDataInterfaceFactory;
        $this->vendorProductFactory = $vendorProductFactory->create();
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
        parent::__construct($data);
    }

    /**
     * {@inheritDoc}
     * @throws NoSuchEntityException
     */
    public function build(
        $vendorId,
        SearchCriteriaInterface $searchCriteria = null
    )
    {
        if (!$searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }
        if ($vendorId) {
            $productIds = array_merge(
                $this->getProductCollectionForSimple($vendorId)->getAllIds(),
                $this->getProductCollectionForConfig($vendorId)->getAllIds()
            );
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addIdFilter($productIds);
            $collection->addAttributeToSelect(['name', 'price']);
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
            $collection->addAttributeToSort('created_at', 'DESC');
            $collection->addAttributeToFilter([['attribute' => 'hide_in_microsite', 'null' => true],
                ['attribute' => 'hide_in_microsite', 'eq' => '0']]);

            /* Added List/Un list Item check */
            //$this->getListedItemsFromCollection($collection, $vendorId);
            /* Added List/Un list Item check */

            /*echo $collection->getSelect(); die();*/
            $this->collectionProcessor->process($searchCriteria, $collection);
            $collection->load();
            $collection->addCategoryIds();
            $productCollection = $collection;
            $this->processCollection($collection, $vendorId);
            $items = [];
            foreach ($collection->getItems() as $item) {
                $items[] = $this->addExtensionAttributes($item);
            }
            $searchResult = $this->searchResultsFactory->create();
            $searchResult->setSearchCriteria($searchCriteria);
            $searchResult->setItems($items);
            $searchResult->setTotalCount($collection->getSize());
            return ['search_result' => $searchResult, 'collection' => $productCollection];

        }
        return [];
    }

    /**
     * @param $vendorId
     * @return void
     */
    public function productCollectionByVendor($vendorId)
    {

    }

    /**
     * @param $entity
     */
    protected function processWishlistFlag($entity)
    {
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            $collection = $this->wishlistCollectionFactory->create()
                ->addCustomerIdFilter($customerId)
                ->addFieldToFilter('product_id', $entity->getId())->getFirstItem();

            if ($collection && $collection->getId()) {
                $entity->setWishlistFlag(true);
            } else {
                $entity->setWishlistFlag(false);
            }
        } else {
            $entity->setWishlistFlag(false);
        }
    }

    /**
     * Add extension attributes to loaded items.
     *
     * @param Collection $collection
     * @return Collection
     */
    private function addExtensionAttributes($item)
    {
        $newItem = $this->productInterfaceFactory->create()->setData($item->getData());
        $newItem->setMediaGalleryEntries($item->getMediaGalleryEntries());
        return $this->readExtensions->execute(
            $newItem
        );
    }

    /**
     * @param $collection
     * @param $vendorId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function processCollection($collection, $vendorId)
    {
        foreach ($collection->getItems() as $entity) {
            if ($entity->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $vendorProduct = $this->getPriceForConfigurable($entity, $vendorId);
            } else {
                $prices = $this->vendorProductResource->getVendorProductPrices(
                    $entity->getId(),
                    $vendorId,
                    $this->storeManager->getStore()->getWebsiteId()
                );
                $vendorProduct = new \Magento\Framework\DataObject($prices);
            }
            $entity->setVendorId($vendorId);
            $entity->setVendorPrice(
                $this->formatPrice(
                    $vendorProduct->getPrice()
                )
            );
            $entity->setVendorSpecialPrice(
                $this->formatPrice(
                    $this->getProcessedPrice($vendorProduct)
                )
            );

            $this->processWishlistFlag($entity);
            $this->mediaGalleryProcessor->execute($entity);
            $entity->setMediaPath(
                $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . 'catalog/product'
            );
            $this->processReviews($entity);
        }
        return $collection;
    }

    /**
     * @param $entity
     * @param $vendorId
     * @return \Magento\Framework\DataObject
     * @throws NoSuchEntityException
     */
    protected function getPriceForConfigurable($entity, $vendorId)
    {
        $productPrice = null;
        $finalPrices = [];
        $vendorProducts = [];
        foreach ($entity->getTypeInstance()->getUsedProducts($entity) as $subProduct) {

            $prices = $this->vendorProductResource->getVendorProductPrices(
                $subProduct->getId(),
                $vendorId,
                $this->storeManager->getStore()->getWebsiteId()
            );

            if (!empty($prices)) {
                $vendorProduct = new \Magento\Framework\DataObject($prices);
                $finalPrices[$subProduct->getId()] = $this->getProcessedPrice($vendorProduct);
                $vendorProducts[$subProduct->getId()] = $vendorProduct;
            }
        }
        if (!empty($finalPrices)) {
            $lowestPriceProductKeys = array_keys($finalPrices, min($finalPrices));
            return $vendorProducts[$lowestPriceProductKeys[0]];
        }
        return new \Magento\Framework\DataObject([]);
    }

    /**
     *
     * @param \Magedelight\Catalog\Model\Product $vendorProduct
     * @return float
     */
    protected function getProcessedPrice($vendorProduct)
    {
        $currentDate = date('Y-m-d', strtotime(date('Y-m-d')));
        $fromDate = $vendorProduct->getData('special_from_date');
        $toDate = $vendorProduct->getData('special_to_date');

        if (empty($fromDate) && empty($toDate)) {

            /* Return special price if set without date(s). */
            return ($vendorProduct->getData('special_price')) ?
                $vendorProduct->getData('special_price') : $vendorProduct->getData('price');
        }

        if (empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d');
        } elseif (!empty($fromDate) && empty($toDate)) {
            $toDate = date('Y-m-d');
        }

        // If current date is between from special date.
        if ($currentDate >= date('Y-m-d', strtotime($fromDate)) && $currentDate <= date('Y-m-d', strtotime($toDate))) {
            return $vendorProduct->getData('special_price');
        }
        return $vendorProduct->getData('price');
    }

    /**
     * @param $entity
     * @return $this
     */
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
     * Retrieve collection processor
     *
     * @return CollectionProcessorInterface
     * @deprecated 102.0.0
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Catalog\Model\Api\SearchCriteria\ProductCollectionProcessor::class
            );
        }
        return $this->collectionProcessor;
    }

    /**
     * @param $vendorId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollectionForConfig($vendorId)
    {
        return $this->vendorProducts->getProductCollectionForConfig($vendorId);
    }

    /**
     * @param $vendorId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollectionForSimple($vendorId)
    {
        return $this->vendorProducts->getProductCollectionForSimple($vendorId);
    }

    /**
     * @param $price
     * @return mixed
     */
    protected function formatPrice($price)
    {
        return $this->catalogHelper->currency(
            $price,
            false,
            false,
            true
        );
    }

    public function buildForFilterAndSorting($productCollection, $vendorId, $storeId)
    {
        if ($vendorId) {
//            $productIds = array_merge(
//                $this->getProductCollectionForSimple($vendorId)->getAllIds(),
//                $this->getProductCollectionForConfig($vendorId)->getAllIds()
//            );
//            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
//            $collection = $this->collectionFactory->create();
//            $collection->addIdFilter($productIds);
//            $collection->addAttributeToSelect(['price']);
//            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
//            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
            $collection = $productCollection;

            $price = $allCategories = [];
            //$_fPrice = [];
            foreach ($collection->getItems() as $item) {
                //$_product = $this->vendorProductFactory->load($item->getId(), 'marketplace_product_id');
                $price[$item->getId()] = $item->getFinalPrice();
                $allCategories = array_merge($allCategories, $item->getCategoryIds());
                //$_fPrice[] = $_product->getFinalPrice();
            }

            /*echo "<pre>"; print_r($price); echo "</pre>";
            echo "<pre>"; print_r($_fPrice); echo "</pre>";
            die();*/

            $vendorCategories = array_unique($allCategories);
            $minPrice = $maxPrice = "0";
            if (!empty($price)) {
                $minPrice = min($price);
                $maxPrice = max($price);
            }
            //$vendor = $this->getVendor($vendorId);
            //$vendorCategories = $vendor->getCategoryIds();

            $vendorCategoryCollection = $this->categoryCollectionFactory->create();
            $vendorCategoryCollection->setStoreId($storeId);
            $vendorCategoryCollection->addAttributeToSelect('name')
                ->addAttributeToFilter('entity_id', ['in' => $vendorCategories])
                ->load();
            $categoryOptions = [];
            foreach ($vendorCategoryCollection as $vendorCategory) {
                $categoryOptions[] = ['label' => $vendorCategory->getName(), 'value' => $vendorCategory->getId()];
            }
            $data = [
                'product_filter' => [
                    [
                        'attribute_code' => 'price',
                        'label' => __('Price'),
                        'options' => [
                            ['label' => __('Min Price'), 'value' => (string)$minPrice],
                            ['label' => __('Max Price'), 'value' => (string)$maxPrice]
                        ]
                    ], [
                        'attribute_code' => 'category_id',
                        'label' => __('Categories'),
                        'options' => $categoryOptions
                    ]
                ],
                'sort_fields' => [
                    [
                        'default' => '',
                        'options' => [
                            ['label' => __('Price'), 'value' => 'price'], ['label' => __('Name'), 'value' => 'name']
                        ]
                    ]
                ]
            ];
            $_data[] = $this->filterAndSortingDataInterfaceFactory->create()->setData($data);
            return $_data;
        }
        return null;
    }

    protected function getVendor($vendorId = null)
    {
        if ($vendorId) {
            $collection = $this->vendorCollectionFactory->create()
                ->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
            $vendor = $collection->_addWebsiteData($this->getVendorWebsiteFields())
                ->getFirstItem();
            if ($vendor && $vendor->getVendorId()) {
                return $vendor;
            }
        }
        return false;
    }

    /**
     * @param $collection
     * @param $vendorId
     * @return void
     * @throws NoSuchEntityException
     *
     */
    public function getListedItemsFromCollection($collection, $vendorId)
    {
        $collection->getSelect()->join(
            ['vprodc' => 'md_vendor_product'],
            "e.entity_id = vprodc.marketplace_product_id AND vprodc.vendor_id = "
            . $vendorId
        );
        $collection->getSelect()->join(
            ['rbvpw' => 'md_vendor_product_website'],
            "rbvpw.vendor_product_id = vprodc.vendor_product_id AND rbvpw.website_id = " . $this->storeManager->getStore()->getWebsiteId() . " AND rbvpw.status = 1",
            ['rbvpw.status']
        );
    }
}
