<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Observer;

use Magento\Framework\Event\ObserverInterface;

class MicrositeIndexAfterMicrositeMassDelete implements ObserverInterface
{

    /**
     * @var \Magedelight\Vendor\Model\Indexer\Microsite\Vendor\Processor $micrositeIndexer
     */
    protected $_micrositeIndexer;
    
    /**
     * @param \Magedelight\Vendor\Model\Indexer\Microsite\Vendor\Processor $micrositeIndexer
     */
    public function __construct(
        \Magedelight\Vendor\Model\Indexer\Microsite\Vendor\Processor $micrositeIndexer
    ) {
        $this->_micrositeIndexer = $micrositeIndexer;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $micrositeIds = $event->getMicrositeIds();
        $this->_micrositeIndexer->reindexList($micrositeIds);
        return $this;
    }
}
