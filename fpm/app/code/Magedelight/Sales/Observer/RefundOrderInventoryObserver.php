<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Observer;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Vendor product inventory return stock
 */
class RefundOrderInventoryObserver implements ObserverInterface
{

    /**
     * @var \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor
     */
    protected $vendorProductIndexer;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product
     */
    protected $vendorProductResource;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;
    
    /**
     *
     * @param StockConfigurationInterface $stockConfiguration
     * @param \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer
     * @param \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer,
        \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->vendorProductResource = $vendorProductResource;
        $this->vendorProductIndexer = $vendorProductIndexer;
    }

    /**
     * Return creditmemo items qty to stock
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $connection = $this->vendorProductResource->getConnection();
        $productTable = $this->vendorProductResource->getMainTable();
        foreach ($creditmemo->getAllItems() as $item) {
            $qty = $item->getQty();
            if (($item->getBackToStock() && $qty) || $this->stockConfiguration->isAutoReturnEnabled()) {
                $orderItem = $item->getOrderItem();
                $vendorId = intval($orderItem->getVendorId());
                // if ($orderItem->getProductType() === "configurable") {
                //     foreach ($orderItem->getChildrenItems() as $childItem) {
                //         $connection->query("update {$productTable} SET qty = qty + {$qty} where vendor_id = {$vendorId} AND marketplace_product_id = {$childItem->getProduct()->getId()}");
                //         $this->vendorProductIndexer->reindexRow($childItem->getProduct()->getId());
                //     }
                // } else {
                //     $connection->query("update {$productTable} SET qty = qty + {$qty} where vendor_id = {$vendorId} AND marketplace_product_id = {$orderItem->getProduct()->getId()}");
                //     $this->vendorProductIndexer->reindexRow($orderItem->getProduct()->getId());
                // }

                if ($orderItem->getProductType() === "simple") {
                            
                    $connection->query("update {$productTable} SET qty = qty + {$orderItem->getQtyOrdered()} where vendor_id = {$vendorId} AND marketplace_product_id = {$orderItem->getProduct()->getId()}");
                }
            }
        }
    }
}
