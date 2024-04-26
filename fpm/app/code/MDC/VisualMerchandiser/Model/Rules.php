<?php 

namespace MDC\VisualMerchandiser\Model;

use Magento\VisualMerchandiser\Model\Rules as MagentoVisualMerchandiserRules;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\VisualMerchandiser\Model\Position\Cache;
use Magento\VisualMerchandiser\Model\Rules\Factory;
use Magento\VisualMerchandiser\Model\Rules\Rule\Collection\Fetcher;
use Magento\VisualMerchandiser\Model\ResourceModel\Rules as ResourceModelRules;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
 
class Rules extends MagentoVisualMerchandiserRules
{
	
	 /**
     * Additional attributes available to smart category rules
     */
    const XML_PATH_AVAILABLE_ATTRIBUTES = 'visualmerchandiser/options/smart_attributes';

    /**
     * @var array
     */
    protected $notices = [];

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @var Factory
     */
    protected $ruleFactory;

    /**
     * @var Fetcher
     */
    protected $fetcher;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $messageManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Attribute $attribute
     * @param Factory $ruleFactory
     * @param Fetcher $fetcher
     * @param array $data
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Cache|null $cache
     * @param DateTime $dateTime
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */

	public function __construct(
		Context $context,
        Registry $registry,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        Attribute $attribute,
        Factory $ruleFactory,
        Fetcher $fetcher,
        array $data = [],
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        Cache $cache = null,
        DateTime $dateTime
	){	
	 	$this->_productCollectionFactory = $productCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_scopeConfig = $scopeConfig;
        $this->attribute = $attribute;
        $this->ruleFactory = $ruleFactory;
        $this->fetcher = $fetcher;
        $this->cache = $cache ?: ObjectManager::getInstance()->get(Cache::class);	
        $this->dateTime = $dateTime;

		parent::__construct($context, $registry, $productCollectionFactory, $storeManager, $messageManager, $scopeConfig, $attribute, $ruleFactory, $fetcher, $data, $resource, $resourceCollection, $cache);
	}


	/**
     * Validate the obvious
     *
     * @return void
     */
    protected function validateData()
    {
        if (!$this->getData('is_active')) {
            return;
        }
        try {
            $conditionsJson = $this->getData('conditions_serialized');
            if ($conditionsJson) {
                \Zend_Json::decode($conditionsJson);
            }
        } catch (\Zend_Exception $e) {
            $this->_messageManager->addException($e, __("Category rules validation failed"));
            $this->setData('conditions_serialized', null);
        }
    }

    /**
     * Get mode
     *
     * @return int
     */
    public function getMode()
    {
        return (int) $this->getData('mode');
    }

    /**
     * Get the attributes usable with VisualMerchandiser rules
     *
     * @return array
     */
    public function getAvailableAttributes()
    {
        $attributesString = $this->_scopeConfig->getValue(self::XML_PATH_AVAILABLE_ATTRIBUTES);
        $attributes = explode(',', $attributesString);
        $attributes = array_map('trim', $attributes);

        $result = [];
        foreach ($attributes as $attributeCode) {
            $attribute = $this->attribute->loadByCode(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeCode
            );
            if (!$attribute->getId()) {
                continue;
            }
            $result[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->addStaticOptions($result);

        asort($result);
        return $result;
    }

    /**
     * Add static options
     *
     * @param array $options
     * @return void
     */
    protected function addStaticOptions(array &$options)
    {
        $options['category_id'] = __('Clone category ID(s)');
        $options['created_at'] = __('Date Created (days ago)');
        $options['updated_at'] = __('Date Modified (days ago)');
    }

    /**
     * Get logic variants
     *
     * @return array
     */
    public static function getLogicVariants()
    {
        return [
            Select::SQL_OR,
            Select::SQL_AND
        ];
    }

    /**
     * Get product collection
     *
     * @return Collection
     *
     * @deprecated 100.3.1
     * @see getProductCollectionByWebsite
     */
    protected function getProductCollection()
    {
        return $this->_productCollectionFactory->create()->setStoreId(
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * Load by category
     *
     * @param Category $category
     * @return Rules
     */
    public function loadByCategory(Category $category)
    {
        return $this->load($category->getId(), 'category_id');
    }

    /**
     * Get conditions
     *
     * @return mixed|null
     * @throws \Zend_Json_Exception
     */
    public function getConditions()
    {
        if (!$this->getId()) {
            return null;
        }

        $conditionsSerialized = $this->getData('conditions_serialized');
        if (!$conditionsSerialized) {
            return null;
        }

        return \Zend_Json::decode($conditionsSerialized);
    }

    /**
     * Apply all rules
     *
     * @param Category $category
     * @param Collection $collection
     * @return void
     */
    public function applyAllRules(Category $category, Collection $collection)
    {
        $rules = $this->loadByCategory($category);

        if (!$rules || !$rules->getIsActive()) {
            return;
        }

        try {
            $conditions = $rules->getConditions();
        } catch (\Zend_Exception $e) {
            $this->_messageManager->addException($e, __("Error in reading category rules"));
            return;
        }

        if (!is_array($conditions) || count($conditions) == 0) {
            $this->_messageManager->addError(__("There was no category rules to apply"));
            return;
        }

        $this->applyConditions($category, $collection, $conditions);

        if (!empty($this->notices)) {
            foreach ($this->notices as $notice) {
                $this->_messageManager->addNotice($notice);
            }
        }

        if ($this->_messageManager->hasMessages()) {
            return;
        }

        $this->_messageManager->addSuccess(__("Category rules applied"));
    }

	public function applyConditions(Category $category, Collection $collection, array $conditions)
    {
        $ids = [];
        $logic = "";
        foreach ($conditions as $rule) {
        	
            $websiteId = (int)$category->getStore()->getWebsiteId();
            $_collection = $this->getProductCollectionByWebsite($websiteId,$rule['attribute']);

            $ruleType = $this->ruleFactory->create($rule);
            $ruleType->applyToCollection($_collection);

            $ids = ($logic == Select::SQL_AND)
                ? array_intersect($ids, $this->fetcher->fetchIds($_collection))
                : array_merge($ids, $this->fetcher->fetchIds($_collection));

            $logic = strtoupper($rule['logic']);

            if ($ruleType->hasNotices()) {
                $this->notices = $this->notices + $ruleType->getNotices();
            }
        }

        $collection->addAttributeToFilter('entity_id', ['in' => $ids]);
        if (count($ids) > 0) {
            $collection->getSelect()->reset(Select::ORDER);
            $collection->getSelect()->order(new \Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $ids) . ')'));
        }

        $positions = $this->getProductsPositions($collection, $ids);

        $category->setPostedProducts($positions);

        // Clear any data that collection cached so far
        if ($collection->isLoaded()) {
            $collection->clear();
        }
    }

    /**
     * Get products positions from cache or regenerate
     *
     * @param Collection $collection
     * @param array $ids
     * @return array
     */
    private function getProductsPositions(Collection $collection, array $ids): array
    {
        $positions = $this->cache->getPositions(Cache::POSITION_CACHE_KEY);
        if (!$positions || count($ids) != count($positions) || array_diff($ids, array_keys($positions))) {
            $positions = [];
            foreach ($collection as $key => $item) {
                /* @var $item \Magento\Catalog\Api\Data\ProductInterface */
                $positions[$item->getId()] = $key;
            }
        }

        return $positions;
    }

    private function getProductCollectionByWebsite(int $websiteId, $ruleAttribute)
    {
        	
        $productCollection = $this->_productCollectionFactory->create()->setStoreId(
            $this->_storeManager->getStore()->getId()
        );
        if ($websiteId) {
            $productCollection->addWebsiteFilter($websiteId);
        }

		/* if VisualMerchandiser category rule with special price is enabled apply */
        if($ruleAttribute === "special_price"){

        	$now = $this->dateTime->date();

	        // $productCollection->addFieldToFilter('special_from_date', ['lteq' => $now]);
	        // $productCollection->addFieldToFilter('special_to_date', ['gteq' => $now]);
    	 	 
	        $productCollection->addAttributeToFilter(
            'special_from_date',['or' => [ 0 => ['lteq' => $now],
                                          1 => ['is' => new \Zend_Db_Expr(
                                             'null'
                                         )],]], 'left'
	        )->addAttributeToFilter(
	            'special_to_date',  ['or' => [ 0 => ['gteq' => $now],
	                                         1 => ['is' => new \Zend_Db_Expr(
	                                             'null'
	                                         )],]], 'left'
	        );

	        $productCollection->getSelect()->join(
			    array('vendor_product' => $productCollection->getTable('md_vendor_product')),
			    'e.entity_id = vendor_product.marketplace_product_id',
			    ["vendor_product.vendor_product_id"]
			);

			 $productCollection->getSelect()->join(
			    array('vendor_product_website' => $productCollection->getTable('md_vendor_product_website')),
			    'vendor_product.vendor_product_id = vendor_product_website.vendor_product_id',
			    []
			)->where("vendor_product_website.special_price > 0")
              ->where("vendor_product_website.special_from_date <= '".$now."' OR vendor_product_website.special_from_date IS NULL ")
              ->where("vendor_product_website.special_to_date >= '".$now."' OR vendor_product_website.special_to_date IS NULL ");			
              
        }
        /* if VisualMerchandiser category rule with special price is enabled apply */
        	
       
        return $productCollection;
    }
}