<?php

namespace CAT\Bnpl\Model\Webapi\Data;

use CAT\Bnpl\Api\Data\BnplCustomerUpdateDataInterface;
use Magento\Framework\DataObject;

class BnplCustomerUpdateData extends DataObject implements BnplCustomerUpdateDataInterface
{

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }
}
