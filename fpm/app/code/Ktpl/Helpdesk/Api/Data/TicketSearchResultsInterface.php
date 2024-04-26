<?php

namespace Ktpl\Helpdesk\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface TicketSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Ktpl\Helpdesk\Api\Data\TicketInterface[]
     */
    public function getItems();

    /**
     * @param \Ktpl\Helpdesk\Api\Data\TicketInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
