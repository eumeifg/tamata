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
namespace Magedelight\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class VendorProductIndex implements ObserverInterface
{

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $_vendorProductFactory;

    /**
     * @var \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor
     */
    protected $_vendorProductIndexer;
    protected $_product;

    /**
     * @var Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stockStateInterface;

    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @param \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer
     * @param \Magedelight\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer,
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->_vendorProductIndexer = $vendorProductIndexer;
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        /* If marketplace id not found , then consider, this operation is being perform from admin panel. */
        /* execute md_vendor_product_listing indexer */
        if ($event->getVendorProduct()->getTypeId() != Configurable::TYPE_CODE) {
            $this->_vendorProductIndexer->reindexRow($event->getVendorProduct()->getMarketplaceProductId());
            $this->_helper->updateProductStockAdmin($event, 'edit');
        }
    }
}
