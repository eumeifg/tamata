<?php
namespace MDC\Sales\Api\Data;

interface OrderSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \MDC\Sales\Api\Data\OrderInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \MDC\Sales\Api\Data\OrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);
}