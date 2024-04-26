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
namespace Magedelight\Catalog\Model\VendorProduct\Listing;

use Magento\Catalog\Model\Product\Type;

/**
 * @api
 * Abstract model for product listing implementation
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
abstract class AbstractProductList
{

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * AbstractProductList constructor.
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getAttributeIdofProductName()
    {
        return $this->attributeRepository->get('catalog_product', 'name')->getId();
    }

    /**
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getAttributeIdofThumbnail()
    {
        return $this->attributeRepository->get('catalog_product', 'thumbnail')->getId();
    }

    /**
     * Add all attributes and apply pricing logic to products collection
     * to get correct values in different products lists.
     * E.g. crosssells, upsells, new products, recently viewed
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param array $attributes
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function _addProductAttributesAndPrices(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        $attributes = ['name','thumbnail']
    ) {
        return $collection
            ->addAttributeToSelect($attributes);
    }

    /**
     * @param integer $vendorId
     * @param integer $storeId
     * @param integer $status
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection($vendorId, $storeId, $status)
    {
        $collection = $this->productCollectionFactory->create();
        /* Exclude stock status filter using 'has_stock_status_filter' inorder to display unlisted products.
         When the product is unlisted, the stock is 0 and the status is 0
        thereby filtering out of stock products. This check is applied in
        frontend by magento and rest_api by us but not in seller area and so it works fine there.*/
        $collection->setFlag('has_stock_status_filter', true);
        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter($storeId);
        $collection = $this->joinExtraTablesForApprovedProducts($collection, $vendorId, $storeId, $status);
        $collection->getSelect()->distinct(true);
        return $collection;
    }

    /**
     *
     * @return string
     */
    public function getProductNameExpression()
    {
        return $expression = new \Zend_Db_Expr("(CASE
            WHEN  cpev.value is NULL
            THEN cpev_default.value
            ELSE cpev.value
          END)");
    }

    /**
     *
     * @return string
     */
    public function getProductThumbnailExpression()
    {
        return $expression = new \Zend_Db_Expr("(CASE
            WHEN  cpev_thumbnail.value is NULL
            THEN cpev_default_thumbnail.value
            ELSE cpev_thumbnail.value
          END)");
    }

    /**
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param integer $vendorId
     * @param $storeId
     * @param integer $status
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function joinExtraTablesForApprovedProducts($collection, $vendorId, $storeId, $status = 0)
    {
        $collection->getSelect()->join(
            ['mdvp' => 'md_vendor_product'],
            'mdvp.marketplace_product_id = e.entity_id AND mdvp.vendor_id = "' . $vendorId . '" '
            . 'AND mdvp.type_id = "' . Type::DEFAULT_TYPE . '"',
            ['vendor_product_id','vendor_sku','qty','type_id','is_offered']
        );

        $collection->getSelect()->join(
            ['mdvpw' => 'md_vendor_product_website'],
            'mdvpw.vendor_product_id = mdvp.vendor_product_id AND mdvpw.status = ' . $status,
            ['price','special_price', 'special_from_date', 'special_to_date','reorder_level']
        );

        $collection->getSelect()->joinLeft(
            ['cpev' => 'catalog_product_entity_varchar'],
            'cpev.row_id = e.row_id AND cpev.attribute_id=' . $this->getAttributeIdofProductName() . ' AND '
            . 'cpev.store_id  =  ' . $storeId . '',
            ['value']
        );

        $collection->getSelect()->joinLeft(
            ['cpev_default' => 'catalog_product_entity_varchar'],
            'cpev_default.row_id = e.row_id AND cpev_default.attribute_id=' . $this->getAttributeIdofProductName() . ' AND '
            . 'cpev_default.store_id  =  0',
            ['value']
        );

        $collection->getSelect()->joinLeft(
            ['cpev_thumbnail' => 'catalog_product_entity_varchar'],
            'cpev_thumbnail.row_id = e.row_id AND cpev_thumbnail.attribute_id=' . $this->getAttributeIdofThumbnail() . ' AND '
            . 'cpev_thumbnail.store_id  =  ' . $storeId . '',
            ['value']
        );

        $collection->getSelect()->joinLeft(
            ['cpev_default_thumbnail' => 'catalog_product_entity_varchar'],
            'cpev_default_thumbnail.row_id = e.row_id AND cpev_default_thumbnail.attribute_id=' . $this->getAttributeIdofThumbnail() . ' AND '
            . 'cpev_default_thumbnail.store_id  =  0',
            ['value']
        );

        $collection->getSelect()->columns(
            [
                'product_name' => $this->getProductNameExpression(),
            ]
        );

        $mediaUrl = $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
        $collection->getSelect()->columns(
            [
                'image' => 'CONCAT("' . $mediaUrl . '",' . $this->getProductThumbnailExpression() . ')',
            ]
        );

        return $collection;
    }
}
