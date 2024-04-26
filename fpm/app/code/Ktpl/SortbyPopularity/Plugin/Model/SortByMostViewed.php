<?php

namespace Ktpl\SortbyPopularity\Plugin\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Mode\Product as Product;
use Magento\Framework\DB\Select;

class SortByMostViewed
{
    const SORT_ORDER_DESC = 'DESC';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * DB connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_conn;

    /**
     * @var ReadExtensions
     */
    protected $readExtensions;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var Product[]
     */
    protected $instances = [];

    /**
     * @var Product[]
     */
    protected $instancesById = [];

    /**
     * @var int
     */
    protected $cacheLimit = 0;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \Magento\Catalog\Model\Layer\Search\CollectionFilter
     */
    protected $collectionFilter;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var Table
     */
    protected $table;

    /**
     * @var AdapterInterface
     */
    protected $_connection;
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Catalog\Model\Layer\Search\CollectionFilter $collectionFilter
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\App\Request\Http $request
     * @param Table $table
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param ReadExtensions|null $readExtensions
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        ResourceConnection $resource,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Catalog\Model\Layer\Search\CollectionFilter $collectionFilter,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\DB\Ddl\Table $table,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        CollectionProcessorInterface $collectionProcessor = null,
        ReadExtensions $readExtensions = null
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
        $this->_conn = $resource->getConnection('catalog');
        $this->_connection = $resource->getConnection();
        $this->readExtensions = $readExtensions ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ReadExtensions::class);
        $this->searchResultsFactory = $searchResultsFactory;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->collectionFilter = $collectionFilter;
        $this->layerResolver = $layerResolver->get();
        $this->request = $request;
        $this->table = $table;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param \Magento\Catalog\Model\ProductRepository $subject
     * @param \Closure $proceed
     * @param $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface|mixed
     * @throws \Zend_Db_Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetList(\Magento\Catalog\Model\ProductRepository $subject, \Closure $proceed, $searchCriteria)
    {
        $path = explode('/', $this->request->getPathInfo());
        if (in_array('mdsearch',$path) || in_array('mdcategories', $path)) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->collectionFactory->create();
            $this->extensionAttributesJoinProcessor->process($collection);
            $collection->addAttributeToSelect('*');
            if ($this->request->getParam('categoryId') === null) {
                $category = $this->layerResolver->getCurrentCategory();
            } else {
                $category = $this->categoryRepository->get($this->request->getParam('categoryId'));
            }
            $this->collectionFilter->filter($collection, $category);
            $para = $this->request->getParam('relevance');
            if ($para !== null) {
                $this->_connection->delete('md_search_store_data_temporary', '1');
                $total = $this->_connection->insertMultiple('md_search_store_data_temporary', $para);
                if ($total > 0) {
                    $collection->getSelect()->joinInner('md_search_store_data_temporary','e.entity_id = md_search_store_data_temporary.entity_id', []);
                }
            }
            /* MDC */
            /* Sort by most_viewed customization */
            if($searchCriteria->getSortOrders() != null) {
                if($searchCriteria->getSortOrders()[0]->getField() === "most_viewed"){
                    $reportEventTable = $collection->getResource()->getTable('report_event');
                    $subSelect = $this->_conn->select()
                        ->from(['report_event_table' => $reportEventTable], 'COUNT(report_event_table.event_id)')
                        ->where('report_event_table.object_id = e.entity_id');
                    $collection->getSelect()->columns(['views' => $subSelect])
                        ->order('views '  . $searchCriteria->getSortOrders()[0]->getDirection());
                    $collection->getSelect()->order('e.entity_id '  . self::SORT_ORDER_DESC);
                } elseif ($searchCriteria->getSortOrders()[0]->getField() === "price") {
                    $collection->getSelect()->order('price_index.min_price '  . $searchCriteria->getSortOrders()[0]->getDirection());
                    $collection->getSelect()->order('e.entity_id '  . self::SORT_ORDER_DESC);
                } elseif ($searchCriteria->getSortOrders()[0]->getField() === "name") {
                    $collection->getSelect()->reset(Select::ORDER);
                }
                elseif ($searchCriteria->getSortOrders()[0]->getField() === "random") {
                    $collection->getSelect()->orderRand();
                }
            } else {
                if ($para) {
                    $collection->getSelect()->order('md_search_store_data_temporary.score '  . self::SORT_ORDER_DESC);
                }
                $collection->getSelect()->order('e.entity_id '  . self::SORT_ORDER_DESC);
            }
            /* MDC */
            $this->collectionProcessor->process($searchCriteria, $collection);
            $collection->load();

            $collection->addCategoryIds();
            $this->addExtensionAttributes($collection);
            $searchResult = $this->searchResultsFactory->create();
            $searchResult->setSearchCriteria($searchCriteria);
            $searchResult->setItems($collection->getItems());
            $searchResult->setTotalCount($collection->getSize());

            foreach ($collection->getItems() as $product) {
                $this->cacheProduct(
                    $this->getCacheKey(
                        [
                            false,
                            $product->getStoreId()
                        ]
                    ),
                    $product
                );
            }
        } else {
            $searchResult = $proceed($searchCriteria);
        }

        return $searchResult;

    }

    /**
     * @return CollectionProcessorInterface|mixed
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Catalog\Model\Api\SearchCriteria\ProductCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }

    /**
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
     * @param $cacheKey
     * @param ProductInterface $product
     */
    private function cacheProduct($cacheKey, ProductInterface $product)
    {
        $this->instancesById[$product->getId()][$cacheKey] = $product;
        $this->saveProductInLocalCache($product, $cacheKey);

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, $offset, null, true);
            $this->instances = array_slice($this->instances, $offset, null, true);
        }
    }

    /**
     * @param $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }
        $serializeData = $this->serializer->serialize($serializeData);
        return sha1($serializeData);
    }

    /**
     * @param ProductInterface $product
     * @param string $cacheKey
     */
    private function saveProductInLocalCache(ProductInterface $product, $cacheKey) : void
    {
        $preparedSku = $this->prepareSku($product->getSku());
        $this->instances[$preparedSku][$cacheKey] = $product;
    }

    /**
     * @param string $sku
     * @return string
     */
    private function prepareSku($sku): string
    {
        return mb_strtolower(trim($sku));
    }

}