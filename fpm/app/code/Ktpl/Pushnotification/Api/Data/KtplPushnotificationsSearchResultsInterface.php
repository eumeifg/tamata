<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Api\Data;


interface KtplPushnotificationsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get ktpl_pushnotifications list.
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param \Ktpl\Pushnotification\Api\Data\KtplPushnotificationsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

