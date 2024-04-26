<?php

namespace Ktpl\Pushnotification\Model;

class Device
{
    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        \Ktpl\Pushnotification\Api\KtplDevicetokensInterface $ktplDevicetokensInterface
    ) {
        $this->request = $request;
        $this->ktplDevicetokensInterface = $ktplDevicetokensInterface;
    }

    public function save($customerId)
    {
        $params = $this->request->getBodyParams();
        $pushpreferences = $params['pushpreferences'] ?? false;
        if(!$pushpreferences) {
            $params = $this->request->getParams();
            $pushpreferences = $params['pushpreferences'] ?? false;
        }

        if ($pushpreferences) {
            $this->ktplDevicetokensInterface->save(
                $pushpreferences['device_type'],
                $pushpreferences['device_token'],
                $pushpreferences['status'] ?? null,
                $customerId
            );
        }
    }
}
