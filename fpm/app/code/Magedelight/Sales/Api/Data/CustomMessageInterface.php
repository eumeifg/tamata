<?php

namespace Magedelight\Sales\Api\Data;

/**
 * Message interface.
 * @api
 */
interface CustomMessageInterface
{
    /*
     * Message.
     */
    const MESSAGE = 'message';
    /*
     * Status.
     */
    const STATUS = 'status';
    
     /**
      * Get Message
      *
      * @return string
      */
    public function getMessage();

    /**
     * Set Message
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Get Status
     *
     * @return bool|string|int
     */
    public function getStatus();

    /**
     * Set Status
     * @param bool|string|int $status
     * @return $this
     */
    public function setStatus($status);
}
