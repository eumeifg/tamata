<?php

namespace Ktpl\ReorderItem\Api\Data;

interface ReorderResponseMessageInterface
{
    const MESSAGE     = 'message';
    const STATUS      = 'status';

     /**
     * get Message
     *
     * @param string $message
     * @return $this
     */
    public function getMessage();
     /**
     * get Status
     *
     * @param boolean $status
     * @return $this
     */
    public function getStatus();

    /**
    * set  Message
    *
    * @param string $message
    * @return $this
    */
    public function setMessage($message);
    /**
    * set  status
    *
    * @param boolean $status
    * @return $this
    */
    public function setStatus($status);
}
