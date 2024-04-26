<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Block\Advocate;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Raf\Model\Advocate\Share\Layout\Processor\InvitationMessage as InvitationMessageProcessor;

/**
 * Class ShareService
 * @package Aheadworks\Raf\Block\Advocate
 */
class ShareService extends Template
{
    /**
     * @var InvitationMessageProcessor
     */
    private $invitationMessageProcessor;

    /**
     * @param Context $context
     * @param InvitationMessageProcessor $invitationMessageProcessor
     * @param array $data
     */
    public function __construct(
        Context $context,
        InvitationMessageProcessor $invitationMessageProcessor,
        array $data = []
    ) {
        $this->invitationMessageProcessor = $invitationMessageProcessor;
        parent::__construct($context, $data);
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout'])
            ? $data['jsLayout']
            : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getJsLayout()
    {
        $this->jsLayout = $this->invitationMessageProcessor->process($this->jsLayout);
        return \Zend_Json::encode($this->jsLayout);
    }
}
