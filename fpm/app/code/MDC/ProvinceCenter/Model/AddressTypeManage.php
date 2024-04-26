<?php
/**
 * Copyright © MDC, All rights reserved.
 */
declare(strict_types=1);

namespace MDC\ProvinceCenter\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use MDC\ProvinceCenter\Api\AddressTypeManageInterface;
use MDC\ProvinceCenter\Api\Data\AddressTypeDataInterface;
use MDC\ProvinceCenter\Model\Data\AddressTypeData;

class AddressTypeManage implements AddressTypeManageInterface
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
    public function saveAddressType(
        $cartId,
        AddressTypeDataInterface $addressType
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
              throw new NoSuchEntityException(
                  __('Cart %1 doesn\'t contain products', $cartId)
              );
        }

        $addressTypeValue = $addressType->getAddressType();

        try {
            $quote->setData(AddressTypeDataInterface::COLUMN_NAME, strip_tags($addressTypeValue));
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('The address type colud not be saved')
            );
        }

        return true;
    }

    public function getQuoteAddressType($cartId){

      $quote = $this->quoteRepository->getActive($cartId);

        if($quote->getData("address_type") === "1"){
          return true;
        }
        else{
         return false;
        }
        
    }
}
