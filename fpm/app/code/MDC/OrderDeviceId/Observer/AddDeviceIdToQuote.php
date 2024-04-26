<?php
/**
 * Copyright © MDC, All rights reserved.
 */
declare(strict_types=1);

namespace MDC\OrderDeviceId\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use MDC\OrderDeviceId\Api\Data\OrderDeviceIdInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response\Renderer\Json;


class AddDeviceIdToQuote implements ObserverInterface
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
        $order = $observer->getEvent()->getOrder();
        if(isset($postData['device_id']) && $postData['device_id']!= '')
        {
            $deviceId = $postData['device_id'];
            $quote->setData("device_id", strip_tags($deviceId));
            $this->quoteRepository->save($quote);
            $order->setData("device_id", $deviceId);
        } else {
            return false;
        }
    }
}
