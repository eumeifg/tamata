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
namespace MDC\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckSalePriceIsAvailable implements ObserverInterface
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
        \MDC\Catalog\Model\PriceProcessor $priceProcessor,
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
        if (!$quote->getId() ||
            !in_array($this->request->getFullActionName(), ['checkout_cart_index','checkout_index_index'])
        ) {
            return;
        }
        $expiredSaleProducts = $this->priceProcessor->getExpiredSaleProducts($quote);
        if (!empty($expiredSaleProducts)) {
            $this->messageManager->addNotice(
                'The price for the product has been expired. You can checkout using product\'s latest price.'
            );
        }
    }
}
