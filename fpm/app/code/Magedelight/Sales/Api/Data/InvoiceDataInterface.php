<?php

namespace Magedelight\Sales\Api\Data;

/**
 * Vendor Invoice interface.
 * @api
 */
interface InvoiceDataInterface
{

    /**
     * Gets the items for the invoice.
     *
     * @return \Magento\Sales\Api\Data\InvoiceItemInterface[]
     */
    public function getItems();

    /**
     * Sets the items for the invoice.
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface[] $items
     * @return $this
     */
    public function setItems($items);

    /**
     * Gets order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    public function getOrder();

    /**
     * Sets order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function setOrder(\Magento\Sales\Api\Data\OrderInterface $order);
}
