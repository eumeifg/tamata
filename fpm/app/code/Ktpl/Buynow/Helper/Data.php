<?php

namespace Ktpl\Buynow\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface  $stockRegistry
     * @param array  $data
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
        parent::__construct($context);
    }

    /**
     * Check for product is instock or not
     *
     * @return int
     */
    public function checkInStockStatus($sku)
    {
        return $this->stockRegistry->getStockItemBySku($sku)->getIsInStock();
    }

   
}
