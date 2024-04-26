<?php
/**
 * Copyright © MDC, All rights reserved.
 */
declare(strict_types=1);

namespace MDC\GetItTogether\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use MDC\GetItTogether\Api\OrderGetItTogetherManagementInterface;
use MDC\GetItTogether\Api\Data\OrderGetItTogetherInterface;
use MDC\GetItTogether\Model\Data\OrderGetItTogether;

class OrderGetItTogetherManagement implements OrderGetItTogetherManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function saveOrderGetItTogether(
        $cartId,
        OrderGetItTogetherInterface $getItTogether
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
              throw new NoSuchEntityException(
                  __('Cart %1 doesn\'t contain products', $cartId)
              );
        }

        $getItTogetherValue = $getItTogether->getGetItTogether();

        try {
            $quote->setData(OrderGetItTogetherInterface::COLUMN_NAME, strip_tags($getItTogetherValue));
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('The Get It Together could not be saved')
            );
        }

        return $getItTogetherValue;
    }

    public function getOrderGetItTogether($cartId){

      $quote = $this->quoteRepository->getActive($cartId);

        if($quote->getData("get_it_together")){
          return true;
        }
        else{
         return false;
        }
        
    }
}
