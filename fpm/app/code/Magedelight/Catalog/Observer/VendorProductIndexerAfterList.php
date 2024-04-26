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

class VendorProductIndexerAfterList implements ObserverInterface
{

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

    /**
     *
     * @param \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     */
    public function __construct(
        \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        \Magento\Catalog\Model\Indexer\Product\Eav\Processor $eavIndexer
    ) {
        $this->_vendorProductIndexer = $vendorProductIndexer;
        $this->_priceIndexer = $priceIndexer;
        $this->_helper = $helper;
        $this->_eavIndexer = $eavIndexer;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        /* If marketplace id not found , then consider, this operation is being perform from admin panel. */
        $productId = ($event->getVendorProduct()->getMarketplaceProductId()) ? : $event->getProduct()->getId();
        /* execute md_vendor_product_listing indexer */
        $this->_vendorProductIndexer->reindexRow($productId);
        /* Commenting as it throws error when configurable/parent product reindexed. */
        /* if ($parentId = $event->getVendorProduct()->getParentId()) {
            $this->_priceIndexer->reindexRow($parentId);
        } */
        /* Commenting as it throws error when configurable/parent product reindexed. */
        $this->_priceIndexer->reindexRow($productId);
        $this->_helper->updateProductStock($event, 'list');
        $this->_eavIndexer->reindexRow($productId);
    }
}
