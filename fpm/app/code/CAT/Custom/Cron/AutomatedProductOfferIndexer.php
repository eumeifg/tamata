<?php

namespace CAT\Custom\Cron;

use Magento\Framework\Event\ManagerInterface as EventManager;
use CAT\Custom\Model\ResourceModel\Automation\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class AutomatedProductOfferIndexer
{
    /**
     * @var DateTime
     */
    protected $_date;
    /**
     * @var EventManager
     */
    private $_eventManager;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @param DateTime $date
     * @param EventManager $eventManager
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        DateTime          $date,
        EventManager      $eventManager,
        CollectionFactory $collectionFactory,
        LoggerInterface   $logger
    )
    {
        $this->_date = $date;
        $this->_eventManager = $eventManager;
        $this->collectionFactory = $collectionFactory;
        $this->_logger = $logger;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('entity_type', ['eq' => 'product_offer']);
        $collection->addFieldToFilter('status', ['eq' => 2]);// status = 2 for the indexing pending records
        if ($collection->getSize()) {
            $productOffer = $collection->getFirstItem();
            $indexerData = $productOffer->getIndexerData();
            $indexerData = !empty($indexerData) ? json_decode($indexerData, true) : '';
            if ($indexerData) {
                $productOffer->setStatus(3)->save(); //indexing started...
                $this->_logger->info('.... Indexer started ....');
                $this->_eventManager->dispatch('vendor_product_mass_list_after', $indexerData);
                $productOffer->setStatus(1)->save(); //indexing completed...
                $this->_logger->info('.... Indexer completed ....');
            }
        }
    }
}
