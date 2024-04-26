<?php
 
declare(strict_types=1);

namespace MDC\GetItTogether\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use MDC\GetItTogether\Api\Data\OrderGetItTogetherInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response\Renderer\Json;


class AddGetItTogetherToQuote implements ObserverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Webapi\Rest\Request $request
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $postData = $this->request->getBodyParams();
        $quote = $observer->getEvent()->getQuote();
        // $order = $observer->getEvent()->getOrder();
        if(isset($postData['get_it_together']) && $postData['get_it_together']!= '')
        {
            $getItTogether = $postData['get_it_together'];
            $quote->setData("get_it_together", $getItTogether);
            $this->quoteRepository->save($quote);
            // $order->setData("get_it_together", $getItTogether);
        } else {
            return false;
        }
    }
}
