<?php

namespace Ktpl\Pushnotification\Model;

class KtplDeviceSavetokens implements \Ktpl\Pushnotification\Api\KtplDevicetokensInterface
{
    protected $deviceTokensModel;
    protected $deviceReposatory;
    protected $dataObjectFactory;
    protected $collectionFactory;

    public function __construct(
        \Ktpl\Pushnotification\Model\Data\KtplDevicetokens $deviceTokensModel,
        \Ktpl\Pushnotification\Api\KtplDevicetokensRepositoryInterface $deviceReposatory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens\CollectionFactory $collectionFactory
    ) {
        $this->deviceTokensModel = $deviceTokensModel;
        $this->deviceReposatory = $deviceReposatory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Save ktpl_devicetokens
     * @param string $device_type
     * @param string $device_token
     * @param bool $status
     * @param int $user_id
     * @return \Ktpl\Pushnotification\Api\Data\KtplDevicetokensSaveInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save($device_type, $device_token, $status = null, $user_id = null)
    {
        $message = '';

        $deviceModel = $this->deviceTokensModel;
        $deviceTokenExists = $this->collectionFactory->create()
        ->addFieldToFilter('device_token', array('eq' => $device_token));
        // echo"<pre>"; print_r($deviceTokenExists->getFirstItem()->getId());die;
        // echo"<pre>"; print_r(get_class_methods($deviceTokenExists));die;
        if($deviceTokenExists->getFirstItem()->getId()) {
            $deviceModel->setId($deviceTokenExists->getFirstItem()->getId());
        }
        $deviceModel->setDeviceType($device_type);
        $deviceModel->setDeviceToken($device_token);
        $deviceModel->setCustomerId($user_id);
        if ($status !== null) {
            $deviceModel->setStatus($status);
        }
        
        $saveData = $this->deviceReposatory->save($deviceModel);
        //echo $saveData->getId();die;
        if($saveData) {
            $message = "Device token saved successfully";
            $deviceModel->setId($saveData->getId());
            //$deviceModel->setId($user_id);
        }

        $result = $this->dataObjectFactory->create();
        $result->setData('message', __($message));
        $result->setData('items', $deviceModel);
        return $result;
    }
}
