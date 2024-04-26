<?php

namespace Ktpl\Pushnotification\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for Ktpl Push Notification List
 *
 * @api
 */
interface KtplPushnotificationDataSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get push notification list
     *
     * @return Ktpl\Pushnotification\Api\Data\KtplPushnotificationDataInterface[]
     */
    public function getItems();

    /**
     * Set push notification list
     *
     * @param  Ktpl\Pushnotification\Api\Data\KtplPushnotificationDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
