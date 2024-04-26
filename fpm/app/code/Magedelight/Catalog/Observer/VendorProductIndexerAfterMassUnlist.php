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

class VendorProductIndexerAfterMassUnlist implements ObserverInterface
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
     *
     * @param \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     */
    public function __construct(
        \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
    ) {
        $this->_vendorProductIndexer = $vendorProductIndexer;
        $this->_helper = $helper;
        $this->_priceIndexer = $priceIndexer;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        
        $marketplaceProductIds = ($event->getMarketplaceProductIds()) ? :  $event->getProductIds();
        if (!empty($marketplaceProductIds)) {
            $this->_vendorProductIndexer->reindexList($marketplaceProductIds);
            $this->_priceIndexer->reindexList($marketplaceProductIds);
        }
        $parentIds = $event->getParentIds();
        if (!empty($parentIds)) {
            $this->_priceIndexer->reindexList($parentIds);
        }
        $this->_helper->updateMultipleProductStock($event, 'unlist', $marketplaceProductIds);
    }
}
