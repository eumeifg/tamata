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
class AddRelatedSellersCountFilterToCollection implements AddFilterToCollectionInterface
{

    protected $filters = [];
    protected $select = [];

    public function __construct()
    {
        $this->select = null;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if ($field == 'related_sellers_count') {
            if (!$this->select) {
                $this->select = $collection->getConnection()->select()->from(
                    ['product_vendor' => 'md_vendor_product'],
                    ['COUNT(product_vendor.vendor_id) as total_sellers']
                )->joinLeft(
                    ['product_vendor_website' => 'md_vendor_product_website'],
                    'product_vendor_website.vendor_product_id = product_vendor.vendor_product_id',
                    []
                )->where('product_vendor.type_id != "configurable"')
                    ->group('product_vendor.marketplace_product_id')
                    ->columns('product_vendor.marketplace_product_id');
            }
        }

        if (isset($condition['gteq']) && $condition['gteq'] && $field == 'related_sellers_count') {
            $this->select->having('total_sellers >= (?)', $condition['gteq']);
        }

        if (isset($condition['lteq']) && $condition['lteq'] && $field == 'related_sellers_count') {
            $this->select->having('total_sellers <= (?)', $condition['lteq']);
        }
        $data = $collection->getConnection()->fetchAll($this->select);

        foreach ($data as $row) {
            $productIds[] = $row['marketplace_product_id'];
        }
        if (!empty($productIds)) {
            $collection->getSelect()->where('e.entity_id IN(?)', $productIds);
        }
    }
}
