<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Api\Data;


interface KtplDevicetokensSaveInterface
{

    /**
     * Get device_type
     * @return string|null
     */
    public function getMessage();

    /**
     * get items
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensInterface
     */
    public function getItems();

}

