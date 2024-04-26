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
namespace Magedelight\Catalog\Model\Autocomplete\Vendor;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Category\Attribute\Source\Layout;
use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Search\Model\QueryFactory;

/**
 * Full text search implementation of autocomplete.
 */
class SearchDataProvider extends \Magento\CatalogSearch\Model\Autocomplete\DataProvider
{
    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    public $vendorProductFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    protected $_layerResolver;

    /**
     * Layout
     *
     * @var Layout
     */
    protected $_layout;

    /**
     * Catalog Product collection
     *
     * @var Collection
     */
    protected $_productCollection;

    /**
     * Image helper
     *
     * @var Image
     */
    protected $_imageHelper;

    /**
     * @var \Magedelight\Vendor\Model\Vendor
     */
    protected $vendor;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_moduleManager;

    /**
     * @param Context $context
     * @param QueryFactory $queryFactory
     * @param ItemFactory $itemFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
     * @param Resolver $layerResolver
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        Resolver $layerResolver,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Module\Manager $moduleManager,
        FlatState $flatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->flatState = $flatState;
        $this->_imageHelper = $context->getImageHelper();
        $this->_layout = $context->getLayout();
        $this->_layerResolver = $layerResolver;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->_layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->vendorProductFactory = $vendorProductFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_request = $request;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_moduleManager = $moduleManager;
        parent::__construct($queryFactory, $itemFactory, $scopeConfig);
    }

    /**
     * Retrieve loaded product collection
     *
     * @return Collection
     */
    public function _getProductCollection()
    {
        if (null === $this->_productCollection) {
            $this->_productCollection = $this->_productCollectionFactory->create()->addAttributeToSelect('*');
        }

        return $this->_productCollection;
    }

    /**
     *
     * @param \Magedelight\Vendor\Model\Vendor $vendor
     * @return $this
     */
    public function setVendor(\Magedelight\Vendor\Model\Vendor $vendor)
    {
        $this->vendor = $vendor;
        return $this;
    }

    /**
     *
     * @return \Magedelight\Vendor\Model\Vendor
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * Get product price
     *
     * @param Product $product
     * @return string
     */
    protected function _getProductPrice($product)
    {
        return $this->_priceCurrency->format(
            $product->getFinalPrice($product),
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $product->getStore()
        );
    }

    protected function getProductData($id)
    {
        $parentByChild = $this->_catalogProductTypeConfigurable->getParentIdsByChild($id);
        if (isset($parentByChild[0])) {
            $id = $parentByChild[0];
        }
        return $id;
    }

    /**
     * Get product reviews
     *
     * @param Product $product
     * @return string
     */
    protected function _getProductReviews($product)
    {
        return $this->_layout->createBlock(\Magento\Review\Block\View::class)
            ->getReviewsSummaryHtml($product, 'short', true);
    }

    /**
     * Product image url getter
     *
     * @param Product $product
     * @return string
     */
    protected function _getImageUrl($product)
    {
        return $this->_imageHelper->init($product, 'product_page_image_small')->getUrl();
    }

    /**
     *
     * @param $product
     * @return string
     */
    public function getSelectUrl($product)
    {
        $cats = $product->getCategoryIds();
        $categoryId = (isset($cats[0]) ? $cats[0] : null);
        return $this->_urlBuilder->getUrl(
            'rbcatalog/product/offer',
            ['cid' => $categoryId, 'pid' => $product->getId(), 'tab'=> '1,2']
        );
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems()
    {
        $collection = $this->_getProductCollection();
        $vendorId = $this->getVendor()->getId();
        $vendorCollection = $this->vendorProductFactory->create()
            ->getCollection()
            ->addFieldToSelect(['marketplace_product_id'])
            ->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId])
            ->addFieldToFilter('type_id', ['neq' => 'configurable']);
        $excludeIds = $vendorCollection->getColumnValues('marketplace_product_id');
        $search = trim($this->_request->getParam('q'));

        /* Flat table Compatibility Changes */
        if ($this->flatState->isAvailable()) {
            $collection->addFieldToFilter([['attribute' => 'name', 'like' => '%' . $search . '%'],
                                                    ['attribute' => 'sku', 'like' => trim($search)]]);
        } else {
            $collection->addAttributeToFilter([['attribute' => 'name', 'like' => '%' . $search . '%'],
                                                    ['attribute' => 'sku', 'like' => trim($search)]]);
        }

        $results = [];
        foreach ($collection as $product) {
            if (in_array($product->getId(), $excludeIds)) {
                continue;
            }
            /** @var \Magento\Catalog\Model\Product $product */
            $results[$product->getId()] = [
                'id'      => $product->getId(),
                'name'    => $product->getName(),
                'price'   => $this->_getProductPrice($product),
                'reviews' => $this->_getProductReviews($product),
                'image'   => $this->_getImageUrl($product),
                'url'     => $this->getSelectUrl($product),
            ];
        }
        return $results;
    }
}
