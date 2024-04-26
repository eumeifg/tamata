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

use Magedelight\Catalog\Api\CategoryProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;

/**
 * Provide product render information (this information should be enough for rendering product on front)
 *
 * Render information provided for one or few products
 */
class CategoryProductRepository implements CategoryProductRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Model\Layer\FilterList
     */
    protected $filterList;
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;
    /**
     * @var CollectionFactory
     */
    protected $wishlistCollectionFactory;
    /**
     * @var \Magedelight\Catalog\Model\ProductRenderSearchResultsFactory
     */
    protected $productRenderSearchResults;
    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Resolver
     */
    protected $layerResolver;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * CategoryProductRepository constructor.
     * @param \Magento\Catalog\Model\Layer\FilterList $filterList
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param CollectionFactory $wishlistCollectionFactory
     * @param \Magedelight\Catalog\Model\ProductRenderSearchResultsFactory $productRenderSearchResults
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\FilterList $filterList,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        CollectionFactory $wishlistCollectionFactory,
        \Magedelight\Catalog\Model\ProductRenderSearchResultsFactory $productRenderSearchResults,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->filterList = $filterList;
        $this->userContext = $userContext;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->productRenderSearchResults = $productRenderSearchResults;
        $this->catalogConfig = $catalogConfig;
        $this->layerResolver = $layerResolver;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritdoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        $categoryId = null,
        $productIds = null
    ) {
        /* If parent category is_anchor attribute is set as yes, then diplay products of all its children.*/
        $category = $this->_initCategory($categoryId);
        $isCategoryFilterExists = false;
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            if ($isCategoryFilterExists == true) {
                continue;
            }
            foreach ($filterGroup->getFilters() as $filter) {
                if ($isCategoryFilterExists == true) {
                    continue;
                }
                if ($filter->getField() == 'category_id') {
                    if (!$category) {
                        $category = $this->_initCategory($filter->getValue());
                    }
                    if ($category->getIsAnchor()) {
                        $childrens = explode(',', $category->getAllChildren());
                        $filter->setValue(implode(',', $childrens));
                        $filter->setField('category_id');
                        if ($childrens > 1) {
                            $filter->setConditionType('in');
                        }
                        $isCategoryFilterExists = true;
                    }
                }
            }
        }
        /* If parent category is_anchor attribute is set as yes, then diplay products of all its children.*/

        $productSearchResult = $this->productRepository->getList($searchCriteria);
        $searchResult = $this->productRenderSearchResults->create();
        $searchResult->setItems($productSearchResult->getItems());
        $searchResult->setTotalCount($productSearchResult->getTotalCount());
        $searchResult->setSearchCriteria($productSearchResult->getSearchCriteria());

        $wishlistProductIds = [];
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            $wishlistCollection = $this->wishlistCollectionFactory->create()
                ->addCustomerIdFilter($customerId);
            if ($wishlistCollection):
                foreach ($wishlistCollection as $item):
                    $wishlistProductIds[] = (int)$item->getProductId();
                endforeach;
            endif;
        }
        $searchResult->setWishlistIds($wishlistProductIds);
        $sortOrdersCollection = $this->loadAvailableOrders();
        $sortOrders = [];
        foreach ($sortOrdersCollection as $key => $value):
            $option = [];
            $option['key'] = $key;
            $option['label'] = $value;

            if ($key == 'position'):
                $option['label'] = $value->getText();
            endif;
            $sortOrders[] = $option;
        endforeach;

        $searchResult->setSortOrders($sortOrders);
        $searchResult->setDirection($this->getSortDirections());

        /* Filters Start */
        $filters = $this->buildFilters($categoryId, $productSearchResult->getItems(), $productIds);
        $searchResult->setFilters($filters);
        return $searchResult;
    }

    protected function _initCategory($categoryId = null)
    {
        if (!$categoryId) {
            return false;
        }

        try {
            $category = $this->categoryRepository->get($categoryId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return $category;
    }

    /**
     * Load Available Orders
     *
     * @return $this
     */
    public function loadAvailableOrders()
    {
        return $this->catalogConfig->getAttributeUsedForSortByArray();
    }

    /**
     * @return array
     */
    private function getSortDirections()
    {
        return ['asc', 'desc'];
    }

    /**
     * @param integer|null $categoryId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function buildFilters($categoryId = null, $items, $productIds = null)
    {
        $data = [];
        if ($categoryId) {
            $layerResolver = $this->layerResolver->get();
            $layerResolver->setCurrentCategory($categoryId);
        } else {
            $layerResolver = $this->layerResolver->get();
            $layerResolver->getProductCollection()->addAttributeToFilter('entity_id', ['in'=>$productIds]);
        }

        $filters = $this->filterList->getFilters($layerResolver);
        foreach ($filters as $filter) {
            if ($filter->getItemsCount()) {
                $filterData = [];
                $filterData['label'] = $filter->getName();
                if ($filter->getRequestVar() == 'cat') {
                    $filterData['code'] = 'category_id';
                } else {
                    $filterData['code'] = $filter->getAttributeModel()->getAttributeCode();
                }
                $filterData['options'] = [];

                foreach ($filter->getItems() as $item) {
                    if ($filterData['code'] === 'price') {
                        // Price filters STARTS
                        $from = $to = '';
                        $convert = html_entity_decode(
                            strip_tags($item->getLabel()->__toString()) . '(' . $item->getCount() . ')'
                        );
                        $filters = (string) $item->getValueString();

                        if ($filters) {
                            $values = explode('-', $filters);
                            if (array_key_exists(0, $values)):
                                $from = $values[0];
                            endif;
                            if (array_key_exists(1, $values)):
                                $to = $values[1];
                            endif;
                        }
                        $filterData['options'][] = [
                            'label' => $convert,
                            'count' => $item->getCount(),
                            'from' => $from,
                            'to' => $to,
                            'id' => (string) $item->getValueString()
                        ];
                    } else {
                        $filterData['options'][] = [
                            'label' => html_entity_decode($item->getLabel() . '(' . $item->getCount() . ')'),
                            'count' => $item->getCount(),
                            'id' => (string) $item->getValueString()
                        ];
                    }
                }
                $data[] = $filterData;
            }
        }
        return $data;
    }
}
