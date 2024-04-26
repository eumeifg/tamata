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

class ReindexProductPrice implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_priceIndexer;
    
    /**
     *
     * @param \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     * @param \Magedelight\Catalog\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        \Magedelight\Catalog\Helper\Data $helper
    ) {
        $this->_vendorProductIndexer = $vendorProductIndexer;
        $this->_priceIndexer = $priceIndexer;
        $this->_helper = $helper;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helper->isEnabled()) {
            $event = $observer->getEvent();
            $productId = $event->getProductId();
            if ($productId) {
                $this->_vendorProductIndexer->reindexRow($productId);
                $this->_priceIndexer->reindexRow($productId);
            }
            $this->_helper->updateProductStockSimple($event, 'list');
        }
    }
}
