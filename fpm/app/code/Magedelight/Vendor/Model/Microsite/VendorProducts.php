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
namespace Magedelight\Vendor\Model\Microsite;

use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;

/**
 * Vendor specific products.
 */
class VendorProducts extends \Magento\Framework\DataObject
{
    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var FlatState
     */
    protected $flatState;

    /**
     * VendorProducts constructor.
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param FlatState $flatState
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        FlatState $flatState
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->flatState = $flatState;
    }

    /**
     * @param $vendorId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollectionForConfig($vendorId)
    {
        $collection = $this->_productCollectionFactory->create();
        if ($this->flatState->isAvailable()) {
            $collection->addFieldToFilter('status', '1');
        } else {
            $collection->addAttributeToFilter('status', '1');
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $collection->getSelect()->join(
            ['vprodc' => 'md_vendor_product'],
            "e.entity_id = vprodc.parent_id AND vprodc.qty > 0 AND vprodc.vendor_id = "
            . $vendorId
        );
        $collection->getSelect()->join(
            ['rbvpw' => 'md_vendor_product_website'],
            "rbvpw.vendor_product_id = vprodc.vendor_product_id AND rbvpw.status = 1",
            ['rbvpw.status']
        );
        return $collection;
    }

    /**
     * @param integer $vendorId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollectionForSimple($vendorId)
    {
        $collection = $this->_productCollectionFactory->create();

        /* Flat table Compatibility Changes */
        if ($this->flatState->isAvailable()) {
            $collection->addFieldToFilter('status', '1');
        } else {
            $collection->addAttributeToFilter('status', '1');
        }

        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection->getSelect()->join(
            ['vprodc' => 'md_vendor_product'],
            "e.entity_id = vprodc.marketplace_product_id AND vprodc.qty > 0 AND vprodc.vendor_id = "
            . $vendorId
        );
        $collection->getSelect()->join(
            ['rbvpw' => 'md_vendor_product_website'],
            "rbvpw.vendor_product_id = vprodc.vendor_product_id AND rbvpw.status = 1",
            ['rbvpw.status']
        );
        return $collection;
    }


}
