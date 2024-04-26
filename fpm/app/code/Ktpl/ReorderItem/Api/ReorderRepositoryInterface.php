<?php

namespace Ktpl\ReorderItem\Api;

use Magento\Quote\Model\Quote;

interface ReorderRepositoryInterface
{
    /**
     * 
     *
     * @api
     * @param int $customerId
     * @param int $orderId
     * @param int $vendorId
     * @param int $itemId
     * @return \Ktpl\ReorderItem\Api\Data\ReorderResponseMessageInterface
     */
    public function reorderItem($customerId, $orderId, $vendorId, $itemId);

    /**
     * Add product to shopping cart (quote)
     *
     * @param int|\Magento\Catalog\Model\Product $productInfo
     * @param array|float|int|\Magento\Framework\DataObject|null $requestInfo
     * @return $this
     */
    public function addProduct($productInfo, $requestInfo = null);

    /**
     * Save cart
     *
     * @return $this
     * @abstract
     */
    public function saveQuote();

    /**
     * Associate quote with the cart
     *
     * @param Quote $quote
     * @return $this
     * @abstract
     */
    public function setQuote(Quote $quote);

    /**
     * Get quote object associated with cart
     *
     * @return Quote
     * @abstract
     */
    public function getQuote();
}