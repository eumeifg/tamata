<?php

namespace Magedelight\Sales\Model;

use Magedelight\Sales\Api\Data\InvoiceDataInterface;

class InvoiceData extends \Magento\Framework\DataObject implements InvoiceDataInterface
{

    /**
     * {@inheritDoc}
     */
    public function getItems()
    {
        return $this->getData('items');
    }

    /**
     * {@inheritDoc}
     */
    public function setItems($items)
    {
        return $this->setData('items', $items);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return $this->getData('order');
    }

    /**
     * {@inheritDoc}
     */
    public function setOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return $this->setData('order', $order);
    }
}
