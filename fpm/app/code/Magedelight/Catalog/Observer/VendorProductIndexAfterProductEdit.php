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

/**
 * Description of VendorProductIndexAfterProductEdi
 *
 * @author Rocket Bazaar
 */
class VendorProductIndexAfterProductEdit implements ObserverInterface
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
        /* If marketplace id not found , then consider, this operation is being perform from admin panel. */
        $productId = $event->getVendorProduct()->getMarketplaceProductId();
        /* execute md_vendor_product_listing indexer */
        $this->_vendorProductIndexer->reindexRow($productId);
        $this->_helper->updateProductStock($event, 'edit');
        $this->_priceIndexer->reindexRow($productId);
    }
}
