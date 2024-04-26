<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Api\Data;


interface KtplDevicetokensSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get ktpl_devicetokens list.
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

