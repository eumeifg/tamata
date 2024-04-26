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

class VendorProductMassIndexer implements ObserverInterface
{

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory
     */
    protected $_defaultVendorIndexersFactory;

    /**
     * @var \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor
     */
    protected $_vendorProductIndexer;

    /**
     *
     * @param \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer
     * @param \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory $defaultVendorsFactory
     */
    public function __construct(
        \Magedelight\Catalog\Model\Indexer\Product\Vendor\Processor $vendorProductIndexer,
        \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory $defaultVendorsFactory
    ) {
        $this->_vendorProductIndexer = $vendorProductIndexer;
        $this->_defaultVendorIndexersFactory = $defaultVendorsFactory->create();
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        
        $marketplaceProductIds = ($event->getMarketplaceProductIds()) ? :  $event->getProductIds();
        
        if ($marketplaceProductIds) {
            $this->_vendorProductIndexer->reindexList($marketplaceProductIds);
        } else {
            if ($ids = $observer->getEvent()->getVendorIds()) {
                $marketplaceProductIds = $this->_defaultVendorIndexersFactory->getVendorProductsByIds($ids);
                if (!empty($marketplaceProductIds)) {
                    $this->_vendorProductIndexer->reindexList($marketplaceProductIds);
                }
            }
        }
    }
}
