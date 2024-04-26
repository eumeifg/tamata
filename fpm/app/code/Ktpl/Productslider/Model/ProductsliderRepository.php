<?php


namespace Ktpl\Productslider\Model;

use Exception;
use Ktpl\Productslider\Api\Data\ProductsliderInterface;
use Ktpl\Productslider\Api\Data\ProductsliderInterfaceFactory;
use Ktpl\Productslider\Api\Data\ProductsliderSearchResultsInterfaceFactory;
use Ktpl\Productslider\Api\ProductsliderRepositoryInterface;
use Ktpl\Productslider\Model\ResourceModel\Slider as ResourceProductslider;
use Ktpl\Productslider\Model\ResourceModel\Slider\CollectionFactory as ProductsliderCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ProductsliderRepository implements ProductsliderRepositoryInterface
{

    protected $productsliderCollectionFactory;
    protected $dataObjectHelper;
    protected $dataObjectProcessor;
    protected $extensionAttributesJoinProcessor;
    protected $resource;
    protected $productsliderFactory;
    protected $searchResultsFactory;
    protected $dataProductsliderFactory;
    protected $extensibleDataObjectConverter;
    private $storeManager;
    private $collectionProcessor;
    /**
     * @var \Ktpl\Productslider\Api\ProductRepositoryInterface
     */
    private $productRepositoryNew;

    /**
     * @param ResourceProductslider $resource
     * @param ProductsliderFactory $productsliderFactory
     * @param ProductsliderInterfaceFactory $dataProductsliderFactory
     * @param ProductsliderCollectionFactory $productsliderCollectionFactory
     * @param ProductsliderSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceProductslider $resource,
        SliderFactory $productsliderFactory,
        ProductsliderInterfaceFactory $dataProductsliderFactory,
        ProductsliderCollectionFactory $productsliderCollectionFactory,
        ProductsliderSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        ProductsliderManagement $productSliderManagement,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTime $date,
        \Ktpl\Productslider\Api\ProductRepositoryInterface $productRepositoryNew
    )
    {
        $this->resource = $resource;
        $this->productsliderFactory = $productsliderFactory;
        $this->productsliderCollectionFactory = $productsliderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataProductsliderFactory = $dataProductsliderFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->date = $date;
        $this->productSliderManagement = $productSliderManagement;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepositoryNew = $productRepositoryNew;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        ProductsliderInterface $productslider
    )
    {
        /* if (empty($productslider->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $productslider->setStoreId($storeId);
        } */

        $productsliderData = $this->extensibleDataObjectConverter->toNestedArray(
            $productslider,
            [],
            ProductsliderInterface::class
        );

        $productsliderModel = $this->productsliderFactory->create()->setData($productsliderData);

        try {
            $this->resource->save($productsliderModel);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the productslider: %1',
                $exception->getMessage()
            ));
        }
        return $productsliderModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        SearchCriteriaInterface $criteria
    )
    {
        $collection = $this->productsliderCollectionFactory->create();

        /*$this->extensionAttributesJoinProcessor->process(
            $collection,
            ProductsliderInterface::class
        );*/
        $collection->addFieldToFilter(['from_date', 'from_date'],[['null' => true],['lteq' => $this->date->date()]])
            ->addFieldToFilter(['to_date', 'to_date'],[['null' => true],['gteq' => $this->date->date()]]);
        $this->collectionProcessor->process($criteria, $collection);

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $productImagePath = $mediaUrl.'catalog/product';

        foreach ($collection->getItems() as $items) {
           $items->setSliderProducts($this->getProductslider($items->getSliderId()));
           $items->setMediaPath($productImagePath);
        }

        /*$items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }*/

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($productsliderId)
    {
        return $this->delete($this->getById($productsliderId));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        ProductsliderInterface $productslider
    )
    {
        try {
            $productsliderModel = $this->productsliderFactory->create();
            $this->resource->load($productsliderModel, $productslider->getSliderId());
            $this->resource->delete($productsliderModel);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Productslider: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($productsliderId)
    {
        $productslider = $this->productsliderFactory->create();
        $this->resource->load($productslider, $productsliderId);
        if (!$productslider->getId()) {
            throw new NoSuchEntityException(__('Productslider with id "%1" does not exist.', $productsliderId));
        }
        return $productslider->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    protected function getProductslider($sliderId)
    {
        $slider = $this->getById($sliderId);
        if (!$slider->getName()) {
            throw new NoSuchEntityException(__('Slider with id "%1" does not exist.', $sliderId));
        }
        $productIds = $this->productSliderManagement->getProductsBySliderType($slider);
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
     * {@inheritdoc}
     */
    public function getListNew(
        SearchCriteriaInterface $criteria
    )
    {
        $collection = $this->productsliderCollectionFactory->create();

        /*$this->extensionAttributesJoinProcessor->process(
            $collection,
            ProductsliderInterface::class
        );*/
        $collection->addFieldToFilter(['from_date', 'from_date'],[['null' => true],['lteq' => $this->date->date()]])
            ->addFieldToFilter(['to_date', 'to_date'],[['null' => true],['gteq' => $this->date->date()]]);
        $this->collectionProcessor->process($criteria, $collection);

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $productImagePath = $mediaUrl.'catalog/product';

//        echo $collection->getSize();
        foreach ($collection->getItems() as $slider) {
            $slider->setSliderProductsNew($this->productRepositoryNew->getList($slider));
            $slider->setMediaPath($productImagePath);
        }

        /*$items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }*/

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

}
