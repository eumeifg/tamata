<?php

namespace CAT\Custom\Cron;

use CAT\Custom\Model\WebApi\ProcessOrder;
use Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory;

class BackgroundInvoiceGenerate
{
    /**
     * @var ProcessOrder
     */
    protected $processOrder;

    /**
     * BackgroundInvoiceGenerate constructor.
     * @param ProcessOrder $processOrder
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ProcessOrder $processOrder,
        CollectionFactory $collectionFactory
    ) {
        $this->processOrder = $processOrder;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function backgroundProcess() {
        $vendorOrderCollection = $this->collectionFactory->create();
        $vendorOrderCollection->addFieldToFilter('in_transit', ['eq' => 1]);
        if ($vendorOrderCollection->getSize()) {
            foreach ($vendorOrderCollection as $vendorOrder) {
                if($this->processOrder->generateInvoice($vendorOrder)) {
                    $vendorOrder->setInTransit(2)->save();
                }
            }
        }
    }
}