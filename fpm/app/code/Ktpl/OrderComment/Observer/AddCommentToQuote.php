<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\OrderComment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Ktpl\OrderComment\Api\Data\OrderCommentInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response\Renderer\Json;


class AddCommentToQuote implements ObserverInterface
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
        if(isset($postData['comment']) && $postData['comment']!= '')
        {
            $comment = $postData['comment'];
            $quote->setData("order_comment", strip_tags($comment));
            $this->quoteRepository->save($quote);
            $order->setData("order_comment", $comment);
        } else {
            return false;
        }
    }
}
