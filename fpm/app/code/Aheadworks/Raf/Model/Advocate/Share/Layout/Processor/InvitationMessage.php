<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Share\Layout\Processor;

use Magento\Framework\Stdlib\ArrayManager;
use Aheadworks\Raf\Model\Advocate\Share\MessageConfig;

/**
 * Class InvitationMessage
 * @package Aheadworks\Raf\Model\Advocate\Share\Layout\Processor
 */
class InvitationMessage
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var MessageConfig
     */
    private $messageConfig;

    /**
     * @param ArrayManager $arrayManager
     * @param MessageConfig $messageConfig
     */
    public function __construct(
        ArrayManager $arrayManager,
        MessageConfig $messageConfig
    ) {
        $this->arrayManager = $arrayManager;
        $this->messageConfig = $messageConfig;
    }

    /**
     * Process js layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        $optionsProviderPath = 'components/awRafMessageConfigProvider';
        $jsLayout = $this->arrayManager->merge(
            $optionsProviderPath,
            $jsLayout,
            [
                'data' => [
                    'messageConfig' => $this->messageConfig->getConfigData()
                ]
            ]
        );

        return $jsLayout;
    }
}
