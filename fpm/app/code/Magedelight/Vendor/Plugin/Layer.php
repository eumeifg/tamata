<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Plugin;

use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;

/**
 * Description of Layer
 *
 * @author Rocket Bazaar Core Team
 */
class Layer
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        FlatState $flatState
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_storeManager = $storeManager;
        $this->request = $request;
        $this->resource = $resource;
        $this->flatState = $flatState;
    }

    public function getProductCollectionForConfig()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $collection->getSelect()->join(
            ['vprodc' => $this->resource->getTableName('md_vendor_product')],
            "e.entity_id = vprodc.parent_id"
        )->where('vprodc.vendor_id = ?', $this->request->getParam('vid'))->where('vprodc.qty > ?', 0);

        $collection->getSelect()->joinLeft(
            ['rbvs'=> $this->resource->getTableName('md_vendor_product_store')],
            'vprodc.vendor_product_id = rbvs.vendor_product_id',
            'store_id'
        )
        ->where('rbvs.store_id = ?', $this->_storeManager->getStore()->getId());

        $collection->getSelect()->joinLeft(
            ['rbvw'=> $this->resource->getTableName('md_vendor_product_website')],
            'vprodc.vendor_product_id = rbvw.vendor_product_id',
            'status'
        )
        ->where('rbvw.status= 1');
        

        return $collection;
    }

    public function aroundPrepareProductCollection(
        \Magento\Catalog\Model\Layer $subject,
        \Closure $proceed,
        $collection
    ) {
        
        if ($this->request->getFullActionName() == 'microsite_vendor_product') {
            $productIds = array_merge(
                $this->getProductCollectionForSimple()->getAllIds(),
                $this->getProductCollectionForConfig()->getAllIds()
            );
            /* Flat table Compatibility Changes */
            if ($this->flatState->isAvailable()) {
                $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);
            }
        }
        else{
            $productIds = array_merge(
                $this->getProductCollectionwithQty()->getAllIds(),
                $this->getProductCollectionwithconfifQty()->getAllIds()
            );
            if ($this->flatState->isAvailable()) {
                $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);
            }
        }
        $result = $proceed($collection);
        return $result;
    }
    public function getProductCollectionwithQty()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->getSelect()->join(
            ['vprodc' => $this->resource->getTableName('md_vendor_product')],
            "e.entity_id = vprodc.marketplace_product_id"
        )->where('vprodc.qty > ?', 0);
        $collection->getSelect()->join(
            ['vprodc2' => 'md_vendor_product_website'],
            "vprodc.vendor_product_id = vprodc2.vendor_product_id"
        )->where('vprodc2.status = ?', 1);
        return $collection;
    }
    public function getProductCollectionwithconfifQty()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->getSelect()->join(
            ['vprodc' => $this->resource->getTableName('md_vendor_product')],
            "e.entity_id = vprodc.parent_id"
        )->where('vprodc.qty > ?', 0);
        $collection->getSelect()->join(
            ['vprodc2' => 'md_vendor_product_website'],
            "vprodc.vendor_product_id = vprodc2.vendor_product_id"
        )->where('vprodc2.status = ?', 1);
        return $collection;
    }

    public function getProductCollectionForSimple()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $collection->getSelect()->join(
            ['vprodc' => $this->resource->getTableName('md_vendor_product')],
            "e.entity_id = vprodc.parent_id"
        )->where('vprodc.vendor_id = ?', $this->request->getParam('vid'))->where('vprodc.qty > ?', 0);
        $collection->getSelect()->joinLeft(
            ['rbvs'=> $this->resource->getTableName('md_vendor_product_store')],
            'vprodc.vendor_product_id = rbvs.vendor_product_id',
            'store_id'
        )
        ->where('rbvs.store_id = ?', $this->_storeManager->getStore()->getId());

        $collection->getSelect()->joinLeft(
            ['rbvw'=> $this->resource->getTableName('md_vendor_product_website')],
            'vprodc.vendor_product_id = rbvw.vendor_product_id',
            'status'
        )
        ->where('rbvw.status= 1');

        return $collection;
    }

}
