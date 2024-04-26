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
namespace Magedelight\Catalog\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * @api
 * @since 100.0.2
 */
class AddVendorFilterToCollection implements AddFilterToCollectionInterface
{

    /**
     * {@inheritdoc}
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (isset($condition['like']) && $condition['like']) {
            $this->addVendorFilter($collection, $condition['like']);
        }
    }

    public function addVendorFilter($collection, $vendor = '')
    {
        $productIds = [];
        $select = $collection->getConnection()->select()->from(
            ['vendor' => 'md_vendor'],
            ['vendor.vendor_id']
        )->joinLeft(
            ['vendor_website' => 'md_vendor_website_data'],
            'vendor_website.vendor_id = vendor.vendor_id',
            ['vendor_website.business_name']
        )->joinLeft(
            ['product_vendor' => 'md_vendor_product'],
            'product_vendor.vendor_id = vendor_website.vendor_id',
            ['product_vendor.marketplace_product_id']
        )->joinLeft(
            ['product_vendor_website' => 'md_vendor_product_website'],
            'product_vendor_website.vendor_product_id = product_vendor.vendor_product_id 
            AND product_vendor_website.status = 1',
            ['website_id']
        )->where(
            'business_name LIKE "'.$vendor.'" OR name LIKE "'.$vendor.'"'
        )->group('product_vendor.marketplace_product_id');
        $data = $collection->getConnection()->fetchAll($select);
        foreach ($data as $row) {
            $productIds[] = $row['marketplace_product_id'];
        }
        if (!empty($productIds)) {
            $collection->getSelect()->where('e.entity_id IN(?)', $productIds);
        }
        return $collection;
    }
}
