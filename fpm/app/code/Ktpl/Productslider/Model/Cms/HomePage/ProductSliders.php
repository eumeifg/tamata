<?php
namespace Ktpl\Productslider\Model\Cms\HomePage;

use Ktpl\Productslider\Model\ResourceModel\Slider\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ktpl\Productslider\Api\ProductRepositoryInterface;
use Ktpl\Productslider\Api\Data\HomePage\ProductSliderInterfaceFactory;
use Ktpl\Productslider\Api\Data\HomePage\SliderCategoryInterfaceFactory;
use MDC\MobileBanner\Model\Cms\HomePage\CategoryBanner;
use Ktpl\Productslider\Api\Data\HomePage\NewItemsBannerInterfaceFactory;
use Ktpl\Productslider\Api\Data\HomePage\BrandsInterfaceFactory;

class ProductSliders extends \Magento\Framework\DataObject
{

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductSliderInterfaceFactory
     */
    protected $productSliderInterfaceFactory;

    /**
     * @var SliderCategoryInterfaceFactory
     */
    protected $sliderCategoryInterfaceFactory;

    /**
     * @var CategoryBanner
     */
    protected $categoryBanner;

    /**
     * @var NewItemsBannerInterfaceFactory
     */
    protected $newItemsBannerInterfaceFactory;

    /**
     * @var BrandsInterfaceFactory
     */
    protected $brandsInterfaceFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param DateTime $date
     * @param ProductRepositoryInterface $productRepository
     * @param ProductSliderInterfaceFactory $productSliderInterfaceFactory
     * @param SliderCategoryInterfaceFactory $sliderCategoryInterfaceFactory
     * @param CategoryBanner $categoryBanner
     * @param NewItemsBannerInterfaceFactory $newItemsBannerInterfaceFactory
     * @param BrandsInterfaceFactory $brandsInterfaceFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        DateTime $date,
        ProductRepositoryInterface $productRepository,
        ProductSliderInterfaceFactory $productSliderInterfaceFactory,
        SliderCategoryInterfaceFactory $sliderCategoryInterfaceFactory,
        CategoryBanner $categoryBanner,
        NewItemsBannerInterfaceFactory $newItemsBannerInterfaceFactory,
        BrandsInterfaceFactory $brandsInterfaceFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->date = $date;
        $this->productRepository = $productRepository;
        $this->productSliderInterfaceFactory = $productSliderInterfaceFactory;
        $this->sliderCategoryInterfaceFactory = $sliderCategoryInterfaceFactory;
        $this->categoryBanner = $categoryBanner;
        $this->newItemsBannerInterfaceFactory = $newItemsBannerInterfaceFactory;
        $this->brandsInterfaceFactory = $brandsInterfaceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $store = $this->storeManager->getStore();
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            ['from_date', 'from_date'],
            [['null' => true], ['lteq' => $this->date->date()]]
        )
        ->addFieldToFilter('location',['like' => '%cms_index_index%'],['like' => '%all_page%'])
        ->addFieldToFilter('store_ids',$store->getId())
        ->addFieldToFilter('status', 1)
        ->addFieldToFilter(
            'customer_group_ids',
            [
                ['finset' => '0'],
                ['finset' => '1']
            ]
        )
        ->addFieldToFilter(
            ['to_date', 'to_date'],
            [['null' => true],['gteq' => $this->date->date()]]
        );
        $collection->setOrder('sort_order', 'ASC');
        $sliders = [];
        $counter = 0;
        foreach ($collection->getItems() as $slider) {
            $_slider = $this->productSliderInterfaceFactory->create()->setData($slider->getData());
            if ($slider->getproduct_type() == 'brand_n_vendor') {
                if (!empty($slider->getBrandVendor())) {
                    $brandAndVendor = json_decode($slider->getBrandVendor(), true);
                    $_brands = [];
                    foreach ($brandAndVendor as $brands) {

                        $brandData = [
                            'image_path' => $brands['images'],
                            'category_id' => $brands['data_id'],
                            'page_type' => $brands['type'],
                            'title' => $brands['title']
                        ];
                        $_brands[] = $this->brandsInterfaceFactory->create()->setData($brandData);
                    }
                }
                $_slider->setBrandsVendors($_brands);
                $_slider->setProducts([]);
                $_slider->setShowViewAllLink(false);
                $_slider->setMediaPath(
                    $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                );
            } elseif($slider->getproduct_type() == 'custom') {
                $products = $this->productRepository->getList($_slider, 5)->getItems();
                $_slider->setProducts($products);
                $_slider->setShowViewAllLink((count($products) > 5) ? true : false);
                $_slider->setMediaPath(
                    $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product'
                );
                $data = ['image_path' => $slider->getNewItemsBanner(), 'page_type' => $slider->getPageType(), 'data_id' => $slider->getDataId()];

                $newItemsBanner = $this->newItemsBannerInterfaceFactory->create()->setData($data);

                $_slider->setNewItemsBanner([$newItemsBanner]);
            } else {
                $products = $this->productRepository->getList($_slider, 5)->getItems();
                $_slider->setProducts($products);
                $_slider->setShowViewAllLink((count($products) > 5) ? true : false);
                $_slider->setMediaPath(
                    $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product'
                );
            }
            $categories = [];
            foreach ($this->categoryBanner->getStaticSliderCategories($counter) as $categoryData){
                $categories[] = $this->sliderCategoryInterfaceFactory->create()->setData($categoryData);
            }

            $_slider->setCategories(
                $categories
            );
            $sliders[] = $_slider;
            $counter++;
        }
        return $sliders;
    }
    /**
     * @param $image
     * @return array|mixed
     */
    public function categoryImageHeight($image) {
        list($width, $height, $type, $attr) = getimagesize($image);
        return $height;
    }
     /**
     * @param $image
     * @return string
     */
    public function categoryImageWidth($image) {
        list($width, $height, $type, $attr) = getimagesize($image);
        return $width;
    }
}
