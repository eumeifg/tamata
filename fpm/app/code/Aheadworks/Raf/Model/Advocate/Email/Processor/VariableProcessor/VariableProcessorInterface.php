<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Email\Processor\VariableProcessor;

/**
 * Interface VariableProcessorInterface
 *
 * @package Aheadworks\Raf\Model\Advocate\Email\Processor\VariableProcessor
 */
interface VariableProcessorInterface
{
    /**
     * Prepare variables before send
     *
     * @param array $variables
     * @return array
     */
    public function prepareVariables($variables);
}
