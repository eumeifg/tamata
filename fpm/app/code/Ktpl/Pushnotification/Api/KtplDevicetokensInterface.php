<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Api;

use Magento\Framework\Api\SearchCriteriaInterface;


interface KtplDevicetokensInterface
{

    /**
     * Save ktpl_devicetokens
     * @param string $device_type
     * @param string $device_token
     * @param bool $status
     * @param string $user_id
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensSaveInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save( $device_type, $device_token, $status = null, $user_id = null);

}

