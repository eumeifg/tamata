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
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

/**
 * @api
 * @since 100.0.2
 */
class AddRelatedSellersCountFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $collection->getSelect()->columns(
            ['related_sellers_count' => $collection->getConnection()->select()->from(
                ['product_vendor' => 'md_vendor_product'],
                ['COUNT(product_vendor.vendor_id) as total_sellers']
            )->joinLeft(
                ['product_vendor_website' => 'md_vendor_product_website'],
                'product_vendor_website.vendor_product_id = product_vendor.vendor_product_id',
                []
            )->where(
                'product_vendor.marketplace_product_id = e.entity_id and product_vendor.type_id != "configurable"'
            )]
        );
    }
}
