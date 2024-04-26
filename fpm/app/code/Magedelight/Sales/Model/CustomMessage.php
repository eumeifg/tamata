<?php

namespace Magedelight\Sales\Model;

use Magedelight\Sales\Api\Data\CustomMessageInterface;

class CustomMessage extends \Magento\Framework\DataObject implements CustomMessageInterface
{
     
     /**
      * {@inheritdoc}
      */
    public function getMessage()
    {
        return $this->getData(CustomMessageInterface::MESSAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        return $this->setData(CustomMessageInterface::MESSAGE, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(CustomMessageInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(CustomMessageInterface::STATUS, $status);
    }
}
