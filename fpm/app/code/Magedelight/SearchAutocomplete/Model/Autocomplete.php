<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_SearchAutocomplete
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\SearchAutocomplete\Model;

use Amasty\Xsearch\Helper\Data;
use Magedelight\SearchAutocomplete\Api\AutocompleteInterface as RBAutocompleteInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Cms\Helper\Page;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Search\Model\AutocompleteInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Autocomplete implements RBAutocompleteInterface
{
    const NEW_API_IS_ACTIVE = 'automation/autosearch_general/is_active';

    /**
     * @var  AutocompleteInterface
     */
    private $autocomplete;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $productCollFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $catCollFactory;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    private $cmsCollFactory;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var Page
     */
    private $cmsHelper;

    /**
     * @var \Magento\Search\Model\ResourceModel\Query\CollectionFactory
     */
    private $searchQueryColl;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepo;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    private $listProduct;

    private $responseData = [];

    private $counter = 0;

    private $resultData;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param AutocompleteInterface $autocomplete
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $productCollFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $catCollFactory
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $cmsCollFactory
     * @param Image $imageHelper
     * @param Page $cmsHelper
     * @param \Magento\Search\Model\ResourceModel\Query\CollectionFactory $searchQueryColl
     * @param CategoryRepositoryInterface $categoryRepo
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param ListProduct $listProduct
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        AutocompleteInterface                                           $autocomplete,
        Data                                                            $helper,
        StoreManagerInterface                                           $storeManager,
        CollectionFactory                                               $productCollFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $catCollFactory,
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory         $cmsCollFactory,
        Image                                                           $imageHelper,
        Page                                                            $cmsHelper,
        \Magento\Search\Model\ResourceModel\Query\CollectionFactory     $searchQueryColl,
        CategoryRepositoryInterface                                     $categoryRepo,
        \Magento\Framework\Pricing\Helper\Data                          $priceHelper,
        ListProduct                                                     $listProduct,
        ScopeConfigInterface                                            $scopeConfig,
        ResourceConnection                                              $resourceConnection
    )
    {
        $this->autocomplete = $autocomplete;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->productCollFactory = $productCollFactory;
        $this->catCollFactory = $catCollFactory;
        $this->cmsCollFactory = $cmsCollFactory;
        $this->imageHelper = $imageHelper;
        $this->cmsHelper = $cmsHelper;
        $this->searchQueryColl = $searchQueryColl;
        $this->categoryRepo = $categoryRepo;
        $this->priceHelper = $priceHelper;
        $this->listProduct = $listProduct;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function getAutoItems($q): array
    {
        $q = trim($q);
        $storeId = $this->storeManager->getStore()->getId();

        $isProductSearch = $this->helper->getModuleConfig('product/enabled');
        $isRecentSearch = $this->helper->getModuleConfig('recent_searches/enabled');
        $isPopularSearch = $this->helper->getModuleConfig('popular_searches/enabled');
        $isCategorySearch = $this->helper->getModuleConfig('category/enabled');
        $isCmsSearch = $this->helper->getModuleConfig('cms/enabled');

        $productSearchLimit = $this->helper->getModuleConfig('product_searches/limit');
        $categorySearchLimit = $this->helper->getModuleConfig('category_searches/limit');
        $cmsSearchLimit = $this->helper->getModuleConfig('cms_searches/limit');
        $popSearchLimit = $this->helper->getModuleConfig('popular_searches/limit');
        $recentSearchLimit = $this->helper->getModuleConfig('recent_searches/limit');
        $newApi = $this->scopeConfig->getValue(self::NEW_API_IS_ACTIVE, ScopeInterface::SCOPE_WEBSITE);
        $searchList = [];
        if ($q == "") {
            $autocompleteData = $this->autocomplete->getItems();
            $this->resultData = $this->getPopularSearch($this->responseData, $isPopularSearch, $q, $autocompleteData, $popSearchLimit);
            $this->resultData = $this->getRecentSearch($this->responseData, $isRecentSearch, $q, $storeId, $recentSearchLimit);
        } else {
            if ($newApi) {
                $storeId = $storeId == 2 ? 2 : 0;
                $this->resultData = $this->getCategorySearchV2($q, $storeId);
            } else {
                try {
                    $autocompleteData = $this->autocomplete->getItems();
                    $this->resultData = $this->getCategorySearch($autocompleteData, $isCategorySearch, $q);
                } catch (NoSuchEntityException|LocalizedException $e) {
                }
            }
            //$this->getProductSearch($this->responseData, $isProductSearch, $q, $storeId);
            //$this->getCmsSearch($this->responseData, $isProductSearch, $q, $storeId);
            //$this->getPopularSearch($this->responseData, $isPopularSearch, $q, $autocompleteData, $popSearchLimit);
            //$this->getRecentSearch($this->responseData, $isRecentSearch, $q, $storeId, $recentSearchLimit);
        }
        return $this->resultData;
    }

    public function getItems($q): array
    {

        $autocompleteData = $this->autocomplete->getItems();
        $responseData = [];
        foreach ($autocompleteData as $resultItem) {
            $responseData[] = $resultItem->toArray();
        }
        return $responseData;
    }

    private function getProductSearch($responseData, $isProductSearch, $q, $storeId)
    {
        if ($isProductSearch) {

            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $storeCode = $this->storeManager->getStore()->getCode();

            $searchCriteriaUrl = "" . $baseUrl . "rest/" . $storeCode . "/V1/search?searchCriteria[requestName]=advanced_search_container&searchCriteria[filterGroups][0][filters][0][field]=name&searchCriteria[filterGroups][0][filters][0][value]=" . $q . "&searchCriteria[pageSize]=4";

            $searchCriteriaUrl = str_replace(" ", '%20', $searchCriteriaUrl);

            $ch = curl_init($searchCriteriaUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            $response = curl_exec($ch);
            $response = json_decode($response, true);
            curl_close($ch);

            $resultItems = $response['items'];
            $resultItemIds = [];
            foreach ($response['items'] as $key => $value) {
                $resultItemIds[] = $value['id'];
            }

            $product = $this->productCollFactory->create()
                ->addStoreFilter($storeId)
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', ['in' => $resultItemIds]);
            // ->addAttributeToFilter([['attribute' => 'name', 'like' => "%" . $q . "%"]])
            // ->addAttributeToFilter('status', ['eq' => 1])
            // ->setPageSize(5)
            // ->setOrder('entity_id', 'DESC');
            $orderIds = implode(",", $resultItemIds);
            $product->getSelect()->order(new \Zend_Db_Expr("FIELD(entity_id,$orderIds)"));

            if (count($product->getData()) > 0) {
                foreach ($product as $productKey => $value) {

                    $this->responseData[$this->counter]['type'] = "Product";
                    $this->responseData[$this->counter]['title'] = $value->getName();
                    $this->responseData[$this->counter]['image'] = $this->imageHelper->init($value, 'product_base_image')->getUrl();
                    $this->responseData[$this->counter]['product_url'] = $value->getProductUrl();
                    // $this->responseData[$this->counter]['price'] = $this->priceHelper->currency($value->getPrice(), true, false);
                    $this->responseData[$this->counter]['price'] = $this->priceHelper->currency($value->getFinalPrice(), true, false);
                    $this->counter++;
                }
            }
        }
        return $this->responseData;
    }

    /**
     * @param $responseData
     * @param $q
     * @param $storeId
     * @return array
     */
    private function getCategorySearchV2($q, $storeId): array
    {
        $productLimit = 10;
        $query = '\''.$q.'\'';

        $sql = "select distinct e.entity_id,v.value as category, e.parent_id ,z.value as category_parent,p.value category_parent_2,l.value category_parent_3
                from catalog_category_entity e join catalog_category_entity_varchar v
                on e.row_id=v.row_id and v.attribute_id='45' and v.store_id = $storeId
                join catalog_category_entity_int i
                on e.row_id=i.row_id and (i.attribute_id='400' and i.value='0')
                join catalog_category_entity_int c
                on e.row_id=c.row_id and (c.attribute_id='69' and c.value='1')
                join catalog_category_entity_int d
                on e.row_id=d.row_id and (d.attribute_id='46' and d.value='1')
                join catalog_category_entity w on (e.parent_id=w.entity_id)
                join catalog_category_entity_varchar z
                on w.row_id=z.row_id and z.attribute_id='45' and z.store_id = $storeId
                join catalog_category_entity_int i1
                on w.row_id=i1.row_id and i1.attribute_id='46' and i1.value='1'
                join catalog_category_entity j on (w.parent_id=j.entity_id)
                join catalog_category_entity_varchar p
                on j.row_id=p.row_id and p.attribute_id='45' and p.store_id = $storeId
                join catalog_category_entity_int i2
                on j.row_id=i2.row_id and i2.attribute_id='46' and i2.value='1'
                join catalog_category_entity u on (j.parent_id=u.entity_id)
                join catalog_category_entity_varchar l
                on u.row_id=l.row_id and l.attribute_id='45' and l.store_id = $storeId
                join catalog_category_entity_int i3
                on u.row_id=i3.row_id and i3.attribute_id='46' and i3.value='1'
                where e.entity_id in
                (select distinct category_id from
                    (select distinct category_id,n.value
                        from catalog_product_entity as e join catalog_product_entity_varchar as n
                        on (e.`row_id` = n.`row_id`) AND ((n.`attribute_id` = '73') or (n.`attribute_id` = '337')) and n.store_id=0
                        join cataloginventory_stock_status as v
                        on e.entity_id = v.product_id
                        JOIN catalog_category_product f ON f.product_id = e.entity_id
                        where v.stock_status =1 and v.qty >3 and MATCH(n.value) AGAINST($query IN NATURAL LANGUAGE MODE)
                        order by category_id
                    ) r
                ) order by e.position ASC limit ".$productLimit;

        $result = $this->connection->fetchAll($sql);

        if (count($result) > 0) {
            $categoryIds = [];
            foreach ($result as $category){
                $categoryParent3 = $category['category_parent_3'] == 'Root Catalog' || $category['category_parent_3'] == 'Default Category' ? null : $category['category_parent_3'] . ' -> ';
                $categoryParent2 = $category['category_parent_2'] == 'Root Catalog' || $category['category_parent_2'] == 'Default Category' ? null : $category['category_parent_2'] . ' -> ';
                $categoryParent = $category['category_parent'] == 'Root Catalog' || $category['category_parent'] == 'Default Category' ? null : $category['category_parent'] . ' -> ';

                if (!in_array($category['entity_id'], $categoryIds) && !isset($this->responseData[$this->counter])) {
                    $this->responseData[$this->counter]['key'] = $q;
                    $this->responseData[$this->counter]['category'] = $categoryParent3 . $categoryParent2 . $categoryParent . $category['category'];
                    $this->responseData[$this->counter]['category_id'] = $category['entity_id'];
                }

                if (!in_array($category['parent_id'], $categoryIds) && !in_array($category['parent_id'], [1, 2])) {
                    $this->counter++;
                    if (!isset($this->responseData[$this->counter])) {
                        $this->responseData[$this->counter]['key'] = $q;
                        $this->responseData[$this->counter]['category'] = $categoryParent3 . $categoryParent2 . $category['category_parent'];
                        $this->responseData[$this->counter]['category_id'] = $category['parent_id'];
                    }
                }
                $categoryIds[] = $category['entity_id'];
                $categoryIds[] = $category['parent_id'];
                $this->counter++;
            }
        }
        return $this->responseData;
    }

    /**
     * @param $responseData
     * @param $isCategorySearch
     * @param $q
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCategorySearch($responseData, $isCategorySearch, $q): array
    {
        if (empty($responseData)) {
            return [];
        }
        if ($isCategorySearch) {
            $productlimit = (count($responseData) > 10) ? 10 : count($responseData);
            $products = $this->productCollFactory->create()->addAttributeToSelect(['entity_id', 'sku'])->addAttributeToFilter(
                [
                    ['attribute' => 'name', 'like' => '%' . $q . '%'],
                    ['attribute' => 'search_terms', 'like' => '%' . $q . '%'],
                ]
            );
            $products->getSelect()->limit($productlimit);
            /*$products->addAttributeToFilter('name', array('like' => "%" . $q . "%"));
            $products->setPageSize($productlimit)->setCurPage(1);*/
            $i = 0;
            foreach ($products as $pkey => $product) {
                $categoryIds = $product->getCategoryIds();
                if (!empty($categoryIds)) {
                    $categoryIds = array_diff($categoryIds, [1, 2]);
                    /*if (($key = array_search('2', $categoryIds)) !== false) {
                        unset($categoryIds[$key]);
                    }*/
                    $category = $this->catCollFactory->create()->setStore($this->storeManager->getStore())
                        ->addAttributeToSelect(['entity_id', 'name'])
                        ->addAttributeToFilter('entity_id', ['in' => $categoryIds])
                        ->addAttributeToFilter('is_active', ['eq' => 1])
                        ->addAttributeToFilter('include_in_menu', ['eq' => 1])
                        ->addAttributeToFilter('is_marketing', ['eq' => 0]);
                    //foreach ($responseData as $popularKey => $popularValue) {
                    foreach ($category as $categoryKey => $value) {
                        $keyword = $responseData[$i]['title'];
                        if (!isset($this->responseData[$this->counter])) {
                            $this->responseData[$this->counter]['key'] = $keyword;
                            $this->responseData[$this->counter]['category'] = $value->getName();//$value->getParentCategory()->getName();
                            $this->responseData[$this->counter]['category_id'] = $value->getId();//$value->getParentCategory()->getId();
                        }
                        if (!in_array($value->getParentCategory()->getId(), [1, 2])) {
                            $this->counter++;
                            if (!isset($this->responseData[$this->counter])) {
                                $this->responseData[$this->counter]['key'] = $keyword;
                                $this->responseData[$this->counter]['category'] = $value->getParentCategory()->getName();
                                $this->responseData[$this->counter]['category_id'] = $value->getParentCategory()->getId();
                            }
                        }
                        $this->counter++;
                    }
                    $i++;
                    //}
                }
            }
        }
        return $this->responseData;
    }

    private function getCmsSearch($responseData, $isCmsSearch, $q, $storeId)
    {
        if ($isCmsSearch) {
            $cms = $this->cmsCollFactory->create()
                ->addStoreFilter($storeId)
                ->addFieldToFilter(['title', 'content'], [['like' => "%" . $q . "%"], ['like' => "%" . $q . "%"]])
                ->addFieldToFilter('is_active', ['eq' => 1]);
            if (count($cms->getData()) > 0) {
                foreach ($cms as $cmsKey => $value) {
                    $this->responseData[$this->counter]['type'] = "cms";
                    $this->responseData[$this->counter]['title'] = $value->getTitle();
                    $this->responseData[$this->counter]['cms_url'] = $this->cmsHelper->getPageUrl($value->getId());
                    $this->counter++;
                }
            }
        }
        return $this->responseData;
    }

    private function getPopularSearch($responseData, $isPopularSearch, $q, $autocompleteData, $popSearchLimit)
    {
        if ($isPopularSearch) {
            $searchCounter = 0;
            $popularSearch = [];
            foreach ($autocompleteData as $key => $resultItem) {
                if (count($resultItem->toArray()) > 0) {
                    if ($searchCounter < $popSearchLimit) {
                        $this->responseData[$this->counter]['type'] = "popular_search";
                        $this->responseData[$this->counter]['title'] = $resultItem->getTitle();
                        $this->responseData[$this->counter]['search_url'] = $this->storeManager->getStore()->getBaseUrl() . "catalogsearch/result/?q=" . str_replace(" ", "+", $resultItem->getTitle());
                        $this->counter++;
                        $searchCounter++;
                    }
                }
            }
        }
        return $this->responseData;
    }

    private function getRecentSearch($responseData, $isRecentSearch, $q, $storeId, $recentSearchLimit)
    {
        if ($isRecentSearch) {
            $query = $this->searchQueryColl->create();
            $query->addStoreFilter($storeId);
            $query->setRecentQueryFilter()->setPageSize($recentSearchLimit);
            $query->getSelect()->where('num_results > 0 AND display_in_terms = 1');
            if (count($query->getData()) > 0) {
                $searchCounter = 0;
                $recentSearchArr = [];
                foreach ($query as $key => $resultItem) {
                    if ($searchCounter <= $recentSearchLimit) {
                        $this->responseData[$this->counter]['type'] = "recent_search";
                        $this->responseData[$this->counter]['title'] = $resultItem->getQueryText();
                        $this->responseData[$this->counter]['search_url'] = $this->storeManager->getStore()->getBaseUrl() . "catalogsearch/result/?q=" . str_replace(" ", "+", $resultItem->getQueryText());
                        $this->counter++;
                    }
                }
            }
        }
        return $this->responseData;
    }

    private function getBreabCrumb($categoryPath)
    {
        $breadcrumb = "";
        $breadcrumbArr = [];
        $breadCrumbList = [];
        foreach (explode("/", $categoryPath) as $key => $value) {
            if ($value != 1 && $value != 2) {
                $category = $this->categoryRepo->get($value);
                $breadcrumb .= $category->getName() . " > ";
                $breadCrumbList[] = $category->getName();
            }
        }
        $breadcrumbArr['breadcrumb'] = $breadCrumbList;
        $breadcrumbArr['name'] = rtrim($breadcrumb, " > ");
        return $breadcrumbArr;
    }
}
