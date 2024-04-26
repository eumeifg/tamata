<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Transaction\Processor;

/**
 * Class Pool
 *
 * @package Aheadworks\Raf\Model\Transaction\Processor
 */
class Pool
{
    /**
     * @var string
     */
    const BASE_PROCESSOR = 'base_processor';

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
     * Retrieve advocate transaction processor by action
     *
     * @param string $type
     * @return ProcessorInterface
     */
    public function getByAction($type)
    {
        if (!isset($this->processors[$type])) {
            $type = self::BASE_PROCESSOR;
        }

        return $this->processors[$type];
    }
}
