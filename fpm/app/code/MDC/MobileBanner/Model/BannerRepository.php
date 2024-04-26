<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MDC\MobileBanner\Model;

use MDC\MobileBanner\Api\BannerRepositoryInterface;
use MDC\MobileBanner\Api\Data\BannerInterfaceFactory;
use MDC\MobileBanner\Api\Data\BannerSearchResultsInterfaceFactory;
use MDC\MobileBanner\Model\ResourceModel\Banner as ResourceBanner;
use MDC\MobileBanner\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class BannerRepository implements BannerRepositoryInterface
{

    protected $extensionAttributesJoinProcessor;

    protected $bannerFactory;

    protected $dataObjectProcessor;

    private $storeManager;

    protected $bannerCollectionFactory;

    protected $searchResultsFactory;

    private $collectionProcessor;

    protected $dataObjectHelper;

    protected $dataBannerFactory;

    protected $resource;

    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceBanner $resource
     * @param BannerFactory $bannerFactory
     * @param BannerInterfaceFactory $dataBannerFactory
     * @param BannerCollectionFactory $bannerCollectionFactory
     * @param BannerSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceBanner $resource,
        BannerFactory $bannerFactory,
        BannerInterfaceFactory $dataBannerFactory,
        BannerCollectionFactory $bannerCollectionFactory,
        BannerSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->bannerFactory = $bannerFactory;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataBannerFactory = $dataBannerFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \MDC\MobileBanner\Api\Data\BannerInterface $banner
    ) {
        /* if (empty($banner->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $banner->setStoreId($storeId);
        } */
        
        $bannerData = $this->extensibleDataObjectConverter->toNestedArray(
            $banner,
            [],
            \MDC\MobileBanner\Api\Data\BannerInterface::class
        );
        
        $bannerModel = $this->bannerFactory->create()->setData($bannerData);
        
        try {
            $this->resource->save($bannerModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the banner: %1',
                $exception->getMessage()
            ));
        }
        return $bannerModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($entityId)
    {
        $banner = $this->bannerFactory->create();
        $this->resource->load($banner, $entityId);
        if (!$banner->getId()) {
            throw new NoSuchEntityException(__('banner with id "%1" does not exist.', $entityId));
        }
        return $banner->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->bannerCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \MDC\MobileBanner\Api\Data\BannerInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \MDC\MobileBanner\Api\Data\BannerInterface $banner
    ) {
        try {
            $bannerModel = $this->bannerFactory->create();
            $this->resource->load($bannerModel, $banner->getEntityId());
            $this->resource->delete($bannerModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the banner: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->get($entityId));
    }
}

