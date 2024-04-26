<?php
 
namespace MDC\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class AppCheckSalePriceIsAvailable implements ObserverInterface
{

    /**
     * @var \Magedelight\Catalog\Model\PriceProcessor
     */
    protected $priceProcessor;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magedelight\Catalog\Model\PriceProcessor $priceProcessor
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magedelight\Catalog\Model\PriceProcessor $priceProcessor,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->priceProcessor = $priceProcessor;
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if (!$quote->getId()) {
            return;
        }
        $expiredSaleProducts = $this->priceProcessor->getExpiredSaleProducts($quote);
    }
}
