<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Email\Processor\Amount;

/**
 * Class Pool
 *
 * @package Aheadworks\Raf\Model\Advocate\Email\Processor\Amount
 */
class Pool
{
    /**#@+
     * Advocate email processors
     */
    const NEW_FRIEND = 'newFriend';
    const EXPIRATION_REMINDER = 'expiration_reminder';
    const EXPIRATION = 'expiration';
    /**#@-*/

    /**
     * @var array
     */
    private $processors;

    /**
     * @param array $processors
     */
    public function __construct(
        array $processors = []
    ) {
        $this->processors = $processors;
    }

    /**
     * Retrieve advocate amount processor
     *
     * @param string $type
     * @return AmountProcessorInterface
     */
    public function get($type)
    {
        if (!isset($this->processors[$type])) {
            throw new \InvalidArgumentException($type . ' is unknown type');
        }

        return $this->processors[$type];
    }
}
