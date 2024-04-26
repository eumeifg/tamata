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
namespace Magedelight\Catalog\Plugin\Model\Layer\Category;

class AddVendorToCategoryListing
{

    /**
     * @param \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider $subject
     * @param $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function afterGetCollection(
        \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider $subject,
        $collection
    ) {
        $collection->getSelect()->joinLeft(
            ['mvpli' => 'md_vendor_product_listing_idx'],
            "e.entity_id = mvpli.marketplace_product_id ",
            ['mvpli.vendor_id']
        );
        return $collection;
    }
}
