<?php

namespace CAT\Custom\Model\Entity;

use CAT\Custom\Helper\Automation;
use CAT\Custom\Model\Source\Option;
use Magento\Framework\App\ResourceConnection;

class VendorQtyUpdate
{
    protected $resourceConnection;
    protected $connection;
    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor
     */
    protected $_vendorProductIndexer;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_priceIndexer;
    
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     */
    protected $_eavIndexer;

    public function __construct(
        Automation $automationHelper,
        ResourceConnection $resourceConnection,
        \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        \Magento\Catalog\Model\Indexer\Product\Eav\Processor $eavIndexer
    ) {
        $this->automationHelper = $automationHelper;
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
        $this->_vendorProductIndexer = $vendorProductIndexer;
        $this->_priceIndexer = $priceIndexer;
        $this->_helper = $helper;
        $this->_eavIndexer = $eavIndexer;
    }

    public function vendorQtyUpdate() {
        $isEnabled = $this->automationHelper->getEntityAutomationEnable(Option::VENDOR_QTY_KEYWORD);
        if ($isEnabled) {
            $limit = $this->automationHelper->getvendorqtyBatchLimit();
            if ($limit) {


                $sql = "UPDATE `cataloginventory_stock_item` as `mpq` JOIN `md_vendor_product` as `vn` on(`vn`.`marketplace_product_id` = `mpq`.`product_id`) JOIN `md_vendor_product_website` as `vns` on(`vns`.`vendor_product_id` = `vn`.`vendor_product_id`) SET `mpq`.`is_in_stock` = '0', `mpq`.`qty` = `vn`.`qty` where `vns`.`status` = 0 and `vn`.`is_offered` = 1" ;

                $results = $this->connection->query($sql);
                $sql = "UPDATE `cataloginventory_stock_item` as `mpq` JOIN `md_vendor_product` as `vn` on(`vn`.`marketplace_product_id` = `mpq`.`product_id`) JOIN `catalog_product_entity` as `mp` on(`mpq`.`product_id` = `mp`.`entity_id`) JOIN `inventory_source_item` as `sq` on(`mp`.`sku` = `sq`.`sku`) JOIN `md_vendor_product_website` as `vns` on(`vns`.`vendor_product_id` = `vn`.`vendor_product_id`) SET `mpq`.`is_in_stock` = '1', `mpq`.`qty` = `vn`.`qty`, `sq`.`quantity` = `vn`.`qty`, `sq`.`status` = `vns`.`status` where (`vn`.`qty` != `mpq`.`qty` or `sq`.`quantity` != `vn`.`qty` or `sq`.`status` != `vns`.`status` or `mpq`.`is_in_stock` != 1) and `vn`.`qty` > 0 and `vns`.`status` = 1";

                $results = $this->connection->query($sql);
                /*foreach($results as $row){

                    $productId = $row['entity_id'];
                    $type_id = $row['type_id'];
                    //execute md_vendor_product_listing indexer 
                    $this->_vendorProductIndexer->reindexRow($productId);
                    $this->_helper->updateStockStatusForProduct($productId,$type_id);
                    $this->_eavIndexer->reindexRow($productId);
                }*/

                /* Updating the process data */

            }
        }
    }
}
