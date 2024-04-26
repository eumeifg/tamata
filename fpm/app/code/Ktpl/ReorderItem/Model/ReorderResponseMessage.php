<?php

namespace Ktpl\ReorderItem\Model;

use Ktpl\ReorderItem\Api\Data\ReorderResponseMessageInterface;

class ReorderResponseMessage extends \Magento\Framework\Model\AbstractExtensibleModel implements ReorderResponseMessageInterface
{
    public function getMessage()
    {
        return $this->_getData(self::MESSAGE);
    } 

    public function getStatus()
    { 
      return $this->_getData(self::STATUS);
    } 

    public function setMessage($message) 
    { 
      return $this->setData(self::MESSAGE, $message);
    } 

    public function setStatus($status) 
    { 
      return $this->setData(self::STATUS, $status);
    }
}