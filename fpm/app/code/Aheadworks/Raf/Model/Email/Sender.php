<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Email;

use Magento\Framework\Mail\Template\TransportBuilder;

/**
 * Class Sender
 *
 * @package Aheadworks\Raf\Model\Email
 */
class Sender
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        TransportBuilder $transportBuilder
    ) {
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * Send email message
     *
     * @param EmailMetadataInterface $emailMetadata
     * @return bool
     * @throws \Magento\Framework\Exception\MailException
     */
    public function send($emailMetadata)
    {
        $this->transportBuilder
            ->setTemplateIdentifier($emailMetadata->getTemplateId())
            ->setTemplateOptions($emailMetadata->getTemplateOptions())
            ->setTemplateVars($emailMetadata->getTemplateVariables())
            ->setFrom(['name' => $emailMetadata->getSenderName(), 'email' => $emailMetadata->getSenderEmail()])
            ->addTo($emailMetadata->getRecipientEmail(), $emailMetadata->getRecipientName());

        $this->transportBuilder->getTransport()->sendMessage();

        return true;
    }
}
