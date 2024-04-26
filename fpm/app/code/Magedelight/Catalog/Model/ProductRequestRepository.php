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
namespace Magedelight\Catalog\Model;

use Magedelight\Catalog\Api\Data\ProductRequestInterfaceFactory;
use Magedelight\Catalog\Api\Data\ProductRequestSearchResultInterfaceFactory;
use Magedelight\Catalog\Model\ResourceModel\Product\Request\Configurable\CollectionFactory as ConfigurableCollectionFactory;
use Magedelight\Catalog\Model\ResourceModel\ProductRequest as ProductRequestResource;
use Magedelight\Catalog\Model\ResourceModel\ProductRequest\Collection;
use Magedelight\Catalog\Model\ResourceModel\ProductRequest\CollectionFactory as RequestCollectionFactory;
use Magedelight\Sales\Api\Data\CustomMessageInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;

class ProductRequestRepository implements \Magedelight\Catalog\Api\ProductRequestRepositoryInterface
{
    /**
     * Cached instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Product Request resource model
     *
     * @var ProductRequestResource
     */
    protected $resource;

    /**
     * Product Request collection factory
     *
     * @var RequestCollectionFactory
     */
    protected $requestCollectionFactory;

    /**
     * Product Request interface factory
     *
     * @var ProductRequestInterfaceFactory
     */
    protected $requestInterfaceFactory;

    /**
     * Data Object Helper
     *
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     *
     * @var ProductRequestSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ConfigurableCollectionFactory
     */
    protected $configurableCollectionFactory;

    /**
     * @var ReadExtensions
     */
    protected $readExtensions;

    /**
     * @var CustomMessageInterface
     */
    protected $customMessageInterface;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * ProductRequestRepository constructor.
     * @param ProductRequestResource $resource
     * @param RequestCollectionFactory $requestCollectionFactory
     * @param ProductRequestInterfaceFactory $requestInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ProductRequestSearchResultInterfaceFactory $searchResultsFactory
     * @param ConfigurableCollectionFactory $configurableCollectionFactory
     * @param ReadExtensions $readExtensions
     * @param CustomMessageInterface $customMessageInterface
     * @param CollectionProcessorInterface $collectionProcessor
     * @param UserContextInterface $userContext
     */
    public function __construct(
        ProductRequestResource $resource,
        RequestCollectionFactory $requestCollectionFactory,
        ProductRequestInterfaceFactory $requestInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        ProductRequestSearchResultInterfaceFactory $searchResultsFactory,
        ConfigurableCollectionFactory $configurableCollectionFactory,
        ReadExtensions $readExtensions,
        CustomMessageInterface $customMessageInterface,
        CollectionProcessorInterface $collectionProcessor,
        UserContextInterface $userContext
    ) {
        $this->resource                      = $resource;
        $this->requestCollectionFactory      = $requestCollectionFactory;
        $this->requestInterfaceFactory       = $requestInterfaceFactory;
        $this->dataObjectHelper              = $dataObjectHelper;
        $this->searchResultsFactory          = $searchResultsFactory;
        $this->readExtensions = $readExtensions;
        $this->customMessageInterface = $customMessageInterface;
        $this->collectionProcessor = $collectionProcessor;
        $this->configurableCollectionFactory = $configurableCollectionFactory;
        $this->userContext = $userContext;
    }

    /**
     * Save Product Request.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductRequestInterface $request
     * @return \Magedelight\Catalog\Api\Data\ProductRequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Catalog\Api\Data\ProductRequestInterface $request)
    {
        /** @var \Magedelight\Catalog\Api\Data\ProductRequestInterface|\Magento\Framework\Model\AbstractModel $request */
        try {
            $this->resource->save($request);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__(
                'Could not save the Product&#x20;Request: %1',
                $exception->getMessage()
            ));
        }
        return $request;
    }

    /**
     * Retrieve Product Request.
     *
     * @param int $requestId
     * @return \Magedelight\Catalog\Api\Data\ProductRequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($requestId)
    {
        if (!isset($this->instances[$requestId])) {
            /** @var \Magedelight\Catalog\Api\Data\ProductRequestInterface|\Magento\Framework\Model\AbstractModel $request */
            $request = $this->requestInterfaceFactory->create();
            $this->resource->load($request, $requestId);
            if (!$request->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested Product doesn\'t exist'));
            }
            $this->instances[$requestId] = $request;
        }
        return $this->instances[$requestId];
    }

    /**
     * Retrieve Product Requests matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Catalog\Api\Data\ProductRequestSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magedelight\Catalog\Api\Data\ProductRequestSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magedelight\Catalog\Model\ResourceModel\ProductRequest\Collection $collection */
        $collection = $this->requestCollectionFactory->create();
        $collection->addWebsiteData(
            $collection,
            null,
            ['price','special_price',  'special_from_date', 'special_to_date','reorder_level']
        );
        $collection->addStoreData($collection);
        $collection->getSelect()->where(
            'main_table.product_request_id NOT IN (SELECT product_request_id from md_vendor_product_request_super_link)'
        );

        if ($this->userContext->getUserId()) {
            $collection->addFieldToFilter('main_table.vendor_id', $this->userContext->getUserId());
        }

        $this->collectionProcessor->process($searchCriteria, $collection);

        $this->addExtensionAttributes($collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Add extension attributes to loaded items.
     *
     * @param Collection $collection
     * @return Collection
     */
    private function addExtensionAttributes(Collection $collection) : Collection
    {
        foreach ($collection->getItems() as $item) {
            $this->readExtensions->execute($item);
        }
        return $collection;
    }

    /**
     * Delete Product Request.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductRequestInterface $request
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Catalog\Api\Data\ProductRequestInterface $request)
    {
        /** @var \Magedelight\Catalog\Api\Data\ProductRequestInterface|\Magento\Framework\Model\AbstractModel $request */
        $id = $request->getId();

        try {
            unset($this->instances[$id]);
            $this->resource->delete($request);
        } catch (\Magento\Framework\Exception\ValidatorException $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove Product %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete Product Request by ID.
     *
     * @param int $requestId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($requestId)
    {
        /* check if product is configurable child exists - if yes then delete them first later */
        $configurableCollection = $this->configurableCollectionFactory->create();
        $configurableCollection->addFieldToFilter('parent_id', $requestId);
        if ($configurableCollection && $configurableCollection->getSize() > 0) {
            foreach ($configurableCollection as $configurable) {
                $configurableRequest = $this->getById($configurable->getProductRequestId());
                $this->delete($configurableRequest);
            }
        }

        $request = $this->getById($requestId);

        return $this->delete($request);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteByIds($ids)
    {
        try {
            $requestIds = [];
            if (strpos($ids, ',') !== false) {
                $requestIds[] =  explode(',', $ids);
            } else {
                $requestIds[] = $ids;
            }
            foreach ($requestIds as $id) {
                /* Delete child products if exists.
                 * Required for configurable products.
                 */
                $collection = $this->requestCollectionFactory->create();
                $collection->getSelect()->join(
                    ['mvprsl' => 'md_vendor_product_request_super_link'],
                    'mvprsl.product_request_id = main_table.product_request_id AND mvprsl.parent_id = ' . $id,
                    ['mvprsl.product_request_id']
                );
                if ($collection && $collection->getSize() > 0) {
                    foreach ($collection as $request) {
                        $request->delete();
                    }
                }
                /* Delete child products if exists. */
            }

            $collection = $this->requestCollectionFactory->create()
                ->addFieldToFilter('product_request_id', ['in' => $requestIds]);

            $deletedRecordsCount = 0;
            if ($collection && $collection->getSize() > 0) {
                foreach ($collection as $request) {
                    $request->delete();
                    $deletedRecordsCount++;
                }
            }

            $this->customMessageInterface->setMessage(
                __('A total of %1 record(s) have been deleted.', $deletedRecordsCount)
            );
            $this->customMessageInterface->setStatus(true);
        } catch (\Exception $e) {
            $this->customMessageInterface->setMessage($e->getMessage());
            $this->customMessageInterface->setStatus(false);
        }
        return $this->customMessageInterface;
    }
}
