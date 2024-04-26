<?php

declare(strict_types=1);

namespace MDC\GetItTogether\Api;

interface OrderGetItTogetherManagementInterface{

	/**
     * @param int $cartId
     * @param \MDC\GetItTogether\Api\Data\OrderGetItTogetherInterface $getItTogether
     * @return null|string
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function saveOrderGetItTogether(
		$cartId,
        \MDC\GetItTogether\Api\Data\OrderGetItTogetherInterface $getItTogether
	);

    /**
     * @param int $cartId
     * @return null|string
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderGetItTogether($cartId);

}