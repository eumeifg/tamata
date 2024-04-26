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

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class VendorProductRepository implements \Magedelight\Catalog\Api\VendorProductRepositoryInterface
{
    /**
     * Cached instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Product resource model
     *
     * @var \Magedelight\Catalog\Model\ResourceModel\VendorProduct
     */
    protected $resource;

    /**
     * Product collection factory
     *
     * @var \Magedelight\Catalog\Model\ResourceModel\VendorProduct\CollectionFactory
     */
    protected $vendorProductCollectionFactory;

    /**
     * Product interface factory
     *
     * @var \Magedelight\Catalog\Api\Data\VendorProductInterfaceFactory
     */
    protected $vendorProductInterfaceFactory;

    /**
     * Data Object Helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     *
     * @var \Magedelight\Catalog\Api\Data\VendorProductSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var VendorProduct\Listing\ApprovedProducts
     */
    protected $approvedProducts;

    /**
     * @var VendorProduct\Listing\LiveProducts
     */
    protected $liveProducts;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * VendorProductRepository constructor.
     * @param ResourceModel\VendorProduct $resource
     * @param ResourceModel\VendorProduct\CollectionFactory $vendorProductCollectionFactory
     * @param \Magedelight\Catalog\Api\Data\VendorProductInterfaceFactory $vendorProductInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magedelight\Catalog\Api\Data\VendorProductSearchResultInterfaceFactory $searchResultsFactory
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param VendorProduct\Listing\LiveProducts $liveProducts
     * @param VendorProduct\Listing\ApprovedProducts $approvedProducts
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Magedelight\Catalog\Model\ResourceModel\VendorProduct $resource,
        \Magedelight\Catalog\Model\ResourceModel\VendorProduct\CollectionFactory $vendorProductCollectionFactory,
        \Magedelight\Catalog\Api\Data\VendorProductInterfaceFactory $vendorProductInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magedelight\Catalog\Api\Data\VendorProductSearchResultInterfaceFactory $searchResultsFactory,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magedelight\Catalog\Model\VendorProduct\Listing\LiveProducts $liveProducts,
        \Magedelight\Catalog\Model\VendorProduct\Listing\ApprovedProducts $approvedProducts,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource                       = $resource;
        $this->vendorProductCollectionFactory = $vendorProductCollectionFactory;
        $this->vendorProductInterfaceFactory  = $vendorProductInterfaceFactory;
        $this->dataObjectHelper               = $dataObjectHelper;
        $this->searchResultsFactory           = $searchResultsFactory;
        $this->userContext                    = $userContext;
        $this->approvedProducts = $approvedProducts;
        $this->liveProducts = $liveProducts;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save Product.
     *
     * @param \Magedelight\Catalog\Api\Data\VendorProductInterface $VendorProduct
     * @return \Magedelight\Catalog\Api\Data\VendorProductInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Catalog\Api\Data\VendorProductInterface $VendorProduct)
    {
        /** @var \Magedelight\Catalog\Api\Data\VendorProductInterface|\Magento\Framework\Model\AbstractModel $VendorProduct */
        try {
            $this->resource->save($VendorProduct);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__(
                'Could not save the Product: %1',
                $exception->getMessage()
            ));
        }
        return $VendorProduct;
    }

    /**
     * Retrieve Product.
     *
     * @param int $VendorProductId
     * @return \Magedelight\Catalog\Api\Data\VendorProductInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($VendorProductId)
    {
        if (!isset($this->instances[$VendorProductId])) {
            /** @var \Magedelight\Catalog\Api\Data\VendorProductInterface|\Magento\Framework\Model\AbstractModel $VendorProduct */
            $VendorProduct = $this->vendorProductInterfaceFactory->create();
            $this->resource->load($VendorProduct, $VendorProductId);
            if (!$VendorProduct->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested Product doesn\'t exist'));
            }
            $this->instances[$VendorProductId] = $VendorProduct;
        }
        return $this->instances[$VendorProductId];
    }

    /**
     * Retrieve Products matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Catalog\Api\Data\VendorProductSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magedelight\Catalog\Api\Data\VendorProductSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Magedelight\Catalog\Model\ResourceModel\VendorProduct\Collection $collection */
        $collection = $this->vendorProductCollectionFactory->create();

        //Add filters from root filter group to the collection
        /** @var \Magento\Framework\Api\Search\FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == \Magento\Framework\Api\SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        } else {
            // set a default sorting order since this method is used constantly in many
            // different blocks
            $field = 'vendor_product_id';
            $collection->addOrder($field, 'ASC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Magedelight\Catalog\Api\Data\VendorProductInterface[] $VendorProducts */
        $VendorProducts = [];
        /** @var \Magedelight\Catalog\Model\VendorProduct $VendorProduct */
        foreach ($collection as $VendorProduct) {
            /** @var \Magedelight\Catalog\Api\Data\VendorProductInterface $VendorProductDataObject */
            $VendorProductDataObject = $this->vendorProductInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $VendorProductDataObject,
                $VendorProduct->getData(),
                \Magedelight\Catalog\Api\Data\VendorProductInterface::class
            );
            $VendorProducts[] = $VendorProductDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($VendorProducts);
    }

    /**
     * Delete Product.
     *
     * @param \Magedelight\Catalog\Api\Data\VendorProductInterface $VendorProduct
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Catalog\Api\Data\VendorProductInterface $VendorProduct)
    {
        /** @var \Magedelight\Catalog\Api\Data\VendorProductInterface|\Magento\Framework\Model\AbstractModel $VendorProduct */
        $id = $VendorProduct->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($VendorProduct);
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
     * {@inheritDoc}
     */
    public function getListingProducts(
        $type,
        $storeId,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        $searchterm = null,
        $outOfStockFilter = false
    ) {
        $vendorId = $this->userContext->getUserId();
        $searchResult = $this->searchResultsFactory->create();

        if (!in_array($type, ['approved', 'live'])) {
            return $searchResult;
        }

        if ($type == 'approved') {
            $collection = $this->approvedProducts->getList(
                $vendorId,
                $storeId
            );
        } elseif ($type == 'live') {
            $collection = $this->liveProducts->getList(
                $vendorId,
                $storeId
            );
        }

        if ($searchterm) {
            $str = preg_replace('/[^A-Za-z0-9()\-\_\ ]/', '', $searchterm);
            $collection->getSelect()->where(
                'cpev.value like "%' . $str . '%" OR cpev_default.value like "%'
                . $str . '%" OR vendor_sku like "%' . $str . '%"'
            );
        }

        if ($outOfStockFilter) {
            $collection->getSelect()->where('mdvp.qty <= 0');
        }
        $collection->setOrder('main_table.vendor_product_id', 'DESC');
        $this->collectionProcessor->process($searchCriteria, $collection);

        $vendorProducts = [];
        foreach ($collection as $item) {
            $vendorProduct = $this->vendorProductInterfaceFactory->create();
            $vendorProduct->setData($item->getData());
            $vendorProduct->setProduct($item);
            $vendorProducts[] = $vendorProduct;
        }
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($vendorProducts);
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * Delete Product by ID.
     *
     * @param int $VendorProductId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($VendorProductId)
    {
        $VendorProduct = $this->getById($VendorProductId);
        return $this->delete($VendorProduct);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magedelight\Catalog\Model\ResourceModel\VendorProduct\Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magedelight\Catalog\Model\ResourceModel\VendorProduct\Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
        return $this;
    }
}
