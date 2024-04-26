<?php

declare(strict_types=1);

namespace MDC\ProvinceCenter\Api;

interface AddressTypeManageInterface{

	/**
     * @param int $cartId
     * @param \MDC\ProvinceCenter\Api\Data\AddressTypeDataInterface $addressType
     * @return null|string
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function saveAddressType(
		$cartId,
        \MDC\ProvinceCenter\Api\Data\AddressTypeDataInterface $addressType
	);

    /**
     * @param int $cartId
     * @return null|string
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuoteAddressType($cartId);

}