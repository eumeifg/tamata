<?php

namespace Ktpl\Pushnotification\Cron;

use Ktpl\Pushnotification\Model\Firebase;

class DeviceTokenCheck
{
    protected $firebase;

    /**
     * @param Firebase $firebase
     */
    public function __construct(
        Firebase $firebase
    ) {
        $this->firebase = $firebase;
    }

    /**
     *
     */
    public function checkDeviceToken() {
        //call the firebase method
        $this->firebase->verifyDevicesIds();
    }
}
