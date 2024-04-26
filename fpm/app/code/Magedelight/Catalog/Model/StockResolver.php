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
namespace Magedelight\Catalog\Model;

use Magento\Catalog\Model\Product\Type;

class StockResolver
{
    /**
     * @var ResourceModel\Product
     */
    private $resourceModel;

    /**
     * StockResolver constructor.
     * @param ResourceModel\Product $resourceModel
     */
    public function __construct(
        \Magedelight\Catalog\Model\ResourceModel\Product $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
    }

    /**
     * Update stock for mass product
     * @param int $productId
     * @param array $stockData
     * @return void
     */
    public function updateMultipleProductStock()
    {
        $connection = $this->resourceModel->getConnection();

        $select = $connection->select()->from(
            ['stock_item' =>$this->resourceModel->getTable('cataloginventory_stock_item')],
            'product_id'
        )->join(
            ['vendor_products' => $this->resourceModel->getTable('md_vendor_product_listing_idx')],
            'vendor_products.marketplace_product_id = stock_item.product_id',
            ['marketplace_product_id']
        )->join(
            ['mdvp' => $this->resourceModel->getTable('md_vendor_product')],
            'mdvp.marketplace_product_id = vendor_products.marketplace_product_id and
             mdvp.type_id = "' . Type::DEFAULT_TYPE . '"',
            ['SUM(mdvp.qty) as qty']
        )->where('stock_item.is_in_stock = ?', 0)
            ->group('stock_item.product_id')
            ->limit(5000);

        foreach ($connection->fetchAll($select) as $row) {
            $data = [
                'is_in_stock'              => 1,
                'qty'              => $row['qty']
            ];
            $where = [
                'product_id' => $row['product_id']
            ];
            $where = sprintf(
                'product_id = %1$d',
                $row['product_id']
            );
            $connection->beginTransaction();
            $connection->update(
                $this->resourceModel->getTable('cataloginventory_stock_item'),
                $data,
                $where
            );
            $data = [
                'stock_status'              => 1,
                'qty'              => $row['qty']
            ];
            $connection->update(
                $this->resourceModel->getTable('cataloginventory_stock_status'),
                $data,
                $where
            );
            $connection->commit();
        }
    }
}
