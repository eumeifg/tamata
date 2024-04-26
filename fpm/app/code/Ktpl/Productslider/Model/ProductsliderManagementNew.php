<?php

namespace Ktpl\Productslider\Model;

use Ktpl\Productslider\Api\ProductsliderManagementInterface;
use Ktpl\Productslider\Api\ProductsliderRepositoryInterface;
use Ktpl\Productslider\Model\Config\Source\ProductType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Reports\Block\Product\Viewed as ReportProductViewed;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellersCollectionFactory;
use Ktpl\Productslider\Model\ResourceModel\Report\Product\CollectionFactory as MostViewedCollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory as WishlistCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Expr;

class ProductsliderManagementNew implements ProductsliderManagementInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    protected $slider;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductsliderRepositoryInterface
     */
    protected $productsliderRepository;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var BestSellersCollectionFactory
     */
    protected $_bestSellersCollectionFactory;

    /**
     * @var MostViewedCollectionFactory
     */
    protected $_mostViewedProductsFactory;

    /**
     * @var ReportProductViewed
     */
    protected $reportProductViewed;

    /**
     * @var WishlistCollectionFactory
     */
    protected $_wishlistCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * ProductsliderManagementNew constructor.
     * @param CategoryFactory $categoryFactory
     * @param CollectionFactory $productCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Visibility $catalogProductVisibility
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param BestSellersCollectionFactory $bestSellersCollectionFactory
     * @param MostViewedCollectionFactory $mostViewedProductsFactory
     * @param ReportProductViewed $reportProductViewed
     * @param WishlistCollectionFactory $wishlistCollectionFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Model\Config $catalogConfig
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Visibility $catalogProductVisibility,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        BestSellersCollectionFactory $bestSellersCollectionFactory,
        MostViewedCollectionFactory $mostViewedProductsFactory,
        ReportProductViewed $reportProductViewed,
        WishlistCollectionFactory $wishlistCollectionFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Config $catalogConfig
    )
    {
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_date = $dateTime;
        $this->_storeManager = $storeManager;
        $this->_bestSellersCollectionFactory = $bestSellersCollectionFactory;
        $this->_mostViewedProductsFactory = $mostViewedProductsFactory;
        $this->reportProductViewed = $reportProductViewed;
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->catalogConfig = $catalogConfig;
    }

    /*public function getAllSliders($searchCriteria)
    {
        $sliderData = [];
        $sliderProducts = $this->productsliderRepository->getList($searchCriteria);
        foreach($sliderProducts->getItems() as $slider) {
            $sliderData[] = $this->getProductslider($slider->getSliderId());
        }
        return $sliderData;
    }*/
    /**
     * {@inheritdoc}
     */
    public function getProductslider($sliderId)
    {
        $slider = $this->getSlider($sliderId);
        $productIds = $this->getProductsBySliderType($slider);
        $productCount = $slider->getLimitNumber() ?: 8;
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'entity_id',
            $productIds,
            "in"
        )->setPageSize($productCount)->create();

        $sliderProducts = $this->productRepository->getList($searchCriteria);
        return $sliderProducts;
    }

    /**
     * @param $sliderId
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSlider($sliderId)
    {
        /*if (!$this->slider) {*/
            $this->slider = $this->productsliderRepository->getById($sliderId);
            if (!$this->slider->getName()) {
                throw new NoSuchEntityException(__('Slider with id "%1" does not exist.', $sliderId));
            }
        /*}*/
        return $this->slider;
    }

    /**
     * @param $slider
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws NoSuchEntityException
     */
    public function getProductsBySliderType($slider)
    {
        if ($slider->getProductType() == ProductType::CATEGORY) {
            return $productIds = $this->getProductIdsByCategory($slider);
        } else if ($slider->getProductType() == ProductType::NEW_PRODUCTS) {
            return $productIds = $this->getProductIdsByNewProducts();
        } else if ($slider->getProductType() == ProductType::BEST_SELLER_PRODUCTS) {
            return $productIds = $this->getProductIdsByBestSellerProducts();
        } else if ($slider->getProductType() == ProductType::FEATURED_PRODUCTS) {
            return $productIds = $this->getProductIdsByFeaturedProducts();
        } else if ($slider->getProductType() == ProductType::MOSTVIEWED_PRODUCTS) {
            return $productIds = $this->getProductIdsByMostViewedProducts();
        } else if ($slider->getProductType() == ProductType::ONSALE_PRODUCTS) {
            return $productIds = $this->getProductIdsByOnSaleProducts();
        } else if ($slider->getProductType() == ProductType::RECENT_PRODUCT) {
            return $productIds = $this->getProductIdsByRecentProducts();
        } else if ($slider->getProductType() == ProductType::WISHLIST_PRODUCT) {
            return $productIds = $this->getProductIdsByWishlistProducts();
        } else if ($slider->getProductType() == ProductType::CUSTOM_PRODUCTS) {
            $productIds = $slider->getProductIds();
            if (!is_array($productIds)) {
                $productIds = explode('&', $productIds);
            }
            $collection = $this->_productCollectionFactory->create()
                ->addIdFilter($productIds)->addAttributeToSelect('*');
            return $collection;
        }
        return [];
    }

    /**
     * Get ProductIds by Category
     *
     * @param $slider
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws NoSuchEntityException
     */
    public function getProductIdsByCategory($slider)
    {
        $catIds = $slider->getCategoriesIds();
        if (is_array($catIds)) {
            foreach ($catIds as $catId) {
                $category = $this->categoryRepository->get($catId);
                $collection = $this->_productCollectionFactory->create();
                $collection->addAttributeToSelect('*')->addCategoryFilter($category)->addAttributeToSort('position', 'ASC');
            }
        } else {
            $collection = $this->_productCollectionFactory->create();
            $category = $this->categoryRepository->get($catIds);
            $collection->addAttributeToSelect('*')->addCategoryFilter($category)->addAttributeToSort('position', 'ASC');
 

        }

        return $collection;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws NoSuchEntityException
     */
    public function getProductIdsByNewProducts()
    {
        $visibleProducts = $this->_catalogProductVisibility->getVisibleInCatalogIds();
        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->setVisibility($visibleProducts)
            ->addAttributeToFilter(
                'news_from_date',
                ['date' => true, 'to' => $this->getEndOfDayDate()],
                'left'
            )
            ->addAttributeToFilter(
                'news_to_date',
                [
                    'or' => [
                        0 => ['date' => true, 'from' => $this->getStartOfDayDate()],
                        1 => ['is' => new Zend_Db_Expr('null')],
                    ]
                ],
                'left'
            )
            ->addAttributeToSort(
                'news_from_date',
                'desc'
            )
            ->addStoreFilter($this->getStoreId());

        return $collection;
    }

    /**
     * Get End of Day Date
     *
     * @return string
     */
    public function getEndOfDayDate()
    {
        return $this->_date->date(null, '23:59:59');
    }

    /**
     * Get Start of Day Date
     *
     * @return string
     */
    public function getStartOfDayDate()
    {
        return $this->_date->date(null, '0:0:0');
    }

    /**
     * Get Store Id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductIdsByBestSellerProducts()
    {
        $productIds = [];
        $collection = $this->_bestSellersCollectionFactory->create()
            ->setPeriod('month');
        if($collection) {
            $productIds = $collection->getColumnValues('product_id');
        }
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*')
            ->addIdFilter($productIds);
        return $collection;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws NoSuchEntityException
     */
    public function getProductIdsByFeaturedProducts()
    {
        $visibleProducts = $this->_catalogProductVisibility->getVisibleInCatalogIds();

        $collection = $this->_productCollectionFactory->create()->setVisibility($visibleProducts);
        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*')
            ->addStoreFilter($this->getStoreId())
            ->addAttributeToFilter('is_featured', '1');
        return $collection;
    }

    /**
     * @return ResourceModel\Report\Product\Collection
     * @throws NoSuchEntityException
     */
    public function getProductIdsByMostViewedProducts()
    {
        $collection = $this->_mostViewedProductsFactory->create()
            ->addAttributeToSelect('*')
            ->setStoreId($this->getStoreId())->addViewsCount()
            ->addStoreFilter($this->getStoreId());
        return $collection;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws NoSuchEntityException
     */
    public function getProductIdsByOnSaleProducts()
    {
        $visibleProducts = $this->_catalogProductVisibility->getVisibleInCatalogIds();
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*')
            ->setVisibility($visibleProducts)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addUrlRewrite()
            ->addAttributeToFilter(
                'special_from_date',
                ['date' => true, 'to' => $this->getEndOfDayDate()],
                'left'
            )->addAttributeToFilter(
                'special_to_date',
                ['or' => [0 => ['date' => true,
                    'from' => $this->getStartOfDayDate()],
                    1 => ['is' => new Zend_Db_Expr(
                        'null'
                    )],]],
                'left'
            )->addAttributeToSort(
                'news_from_date',
                'desc'
            )->addStoreFilter($this->getStoreId());
        return $collection;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductIdsByRecentProducts()
    {
        return $this->_productCollectionFactory->create()->addIdFilter(
            $this->reportProductViewed->getItemsCollection()->getAllIds()
        )->addAttributeToSelect('*');
    }

    /**
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws NoSuchEntityException
     */
    public function getProductIdsByWishlistProducts()
    {
        if ($this->_customer->isLoggedIn()) {
            $wishlist = $this->_wishlistCollectionFactory->create()
                ->addCustomerIdFilter($this->_customer->getCustomerId());
            $productIds = null;

            foreach ($wishlist as $product) {
                $productIds[] = $product->getProductId();
            }
            $collection = $this->_productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addIdFilter($productIds);
            $collection = $this->_addProductAttributesAndPrices($collection)
                ->addStoreFilter($this->getStoreId());
            return $collection;
        }
        return [];
    }
}
