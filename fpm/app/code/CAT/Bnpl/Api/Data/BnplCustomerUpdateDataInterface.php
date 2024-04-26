<?php

namespace CAT\Bnpl\Api\Data;

interface BnplCustomerUpdateDataInterface
{
    const STATUS = 'status';
    const MESSAGE = 'message';
    const DATA = 'data';

    /**
     * @return bool
     */
    public function getStatus();

    /**
     * @param bool $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message);
}
