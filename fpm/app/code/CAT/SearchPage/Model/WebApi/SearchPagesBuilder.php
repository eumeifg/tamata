<?php

namespace CAT\SearchPage\Model\WebApi;

use CAT\SearchPage\Api\SearchPagesBuilderInterface;
use CAT\SearchPage\Api\Data\SearchPagesBuilderDataInterface;
use CAT\SearchPage\Api\Data\BannerSliderDataInterfaceFactory;
use CAT\SearchPage\Model\ResourceModel\SearchPage\CollectionFactory;
use Ktpl\Productslider\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Collection;
use Ktpl\Productslider\Api\Data\ProductsliderInterfaceFactory;

class SearchPagesBuilder implements SearchPagesBuilderInterface
{
    /**
     * @var CollectionFactory
     */
    protected $searchPageCollectionFactory;

    /**
     * @var SearchPagesBuilderDataInterface
     */
    protected $searchPageBuilderData;

    /**
     * @var BannerSliderDataInterfaceFactory
     */
    protected $bannerSliderDataFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var Collection
     */
    protected $dataCollection;
    /**
     * @var ProductsliderInterfaceFactory
     */
    protected $productSliderFactory;

    /**
     * @param CollectionFactory $searchPageCollectionFactory
     * @param SearchPagesBuilderDataInterface $searchPageBuilderData
     * @param BannerSliderDataInterfaceFactory $bannerSliderDataFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Collection $dataCollection
     * @param ProductsliderInterfaceFactory $productSliderFactory
     */
    public function __construct(
        CollectionFactory                $searchPageCollectionFactory,
        SearchPagesBuilderDataInterface  $searchPageBuilderData,
        BannerSliderDataInterfaceFactory $bannerSliderDataFactory,
        ProductRepositoryInterface       $productRepository,
        Collection                       $dataCollection,
        ProductsliderInterfaceFactory    $productSliderFactory
    )
    {
        $this->searchPageCollectionFactory = $searchPageCollectionFactory;
        $this->searchPageBuilderData = $searchPageBuilderData;
        $this->bannerSliderDataFactory = $bannerSliderDataFactory;
        $this->productRepository = $productRepository;
        $this->dataCollection = $dataCollection;
        $this->productSliderFactory = $productSliderFactory;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function searchPage(): SearchPagesBuilderDataInterface
    {
        $collection = $this->getSearchPageCollection();
        if ($collection->getSize()) {
            $searchPage = $collection->getFirstItem();
            $additionalInfo = json_decode($searchPage->getAdditionalInfo());
            $sliders = [];
            if (count($additionalInfo) > 0) {

                foreach ($additionalInfo as $item) {
                    $productSlider = $this->productSliderFactory->create();
                    if ($item->best_seller == 1) {
                        $productSlider->setCategoriesIds($item->best_seller_id);
                        $productSlider->setProductType('best-seller');
                    } else {
                        $productSlider->setCategoriesIds($item->best_seller_id);
                        $productSlider->setProductType('category');
                    }

                    $products = $this->productRepository->getList($productSlider, '10')->getItems();
                    $showViewAllLink = !($item->best_seller == 1);
                    $bannerSlider = $this->bannerSliderDataFactory->create();
                    $bannerSlider->setBannerUrl($item->banner[0]->url);
                    $bannerSlider->setPageType($item->page_type);
                    $bannerSlider->setDataId($item->data_id);
                    $bannerSlider->setCategoryTitle($item->title);
                    $bannerSlider->setShowViewAllLink($showViewAllLink);
                    $bannerSlider->setViewAllLinkId($item->best_seller_id);
                    $bannerSlider->setProducts($products);
                    $sliders[] = $bannerSlider;
                }
            }
            $this->searchPageBuilderData->setBannerSlider($sliders);
        }
        return $this->searchPageBuilderData;
    }

    /**
     * @return mixed
     */
    public function getSearchPageCollection()
    {
        $searchPageCollection = $this->searchPageCollectionFactory->create();
        $searchPageCollection->addFieldToSelect('additional_info');
        $searchPageCollection->addFieldToFilter('status', ['eq' => 1]);
        return $searchPageCollection;
    }
}
