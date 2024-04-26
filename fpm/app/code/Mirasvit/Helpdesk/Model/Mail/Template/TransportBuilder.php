<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-helpdesk
 * @version   1.1.96
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Helpdesk\Model\Mail\Template;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder implements TransportBuilderInterface
{
    protected $attachments = [];
    protected $productMetadata;
    protected $moduleManager;

    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        ProductMetadataInterface $productMetadata,
        Manager $moduleManager
    ) {
        parent::__construct($templateFactory, $message, $senderResolver, $objectManager, $mailTransportFactory);
        $this->productMetadata = $productMetadata;
        $this->moduleManager = $moduleManager;
        $this->reset();
    }

    /**
     * {@inheritdoc}
     */
    public function addAttachment(
        $body,
        $mimeType = \Zend_Mime::TYPE_OCTETSTREAM,
        $disposition = \Zend_Mime::DISPOSITION_ATTACHMENT,
        $encoding = \Zend_Mime::ENCODING_BASE64,
        $filename = null
    ) {

        if ($this->hasBuiltInAttachmentFunction()) {
            return $this->message->createAttachment($body, $mimeType, $disposition, $encoding, $filename);
        }

        if ($body instanceof \Fooman\EmailAttachments\Model\Api\AttachmentInterface &&
            $this->moduleManager->isEnabled('Fooman_EmailAttachments')
        ) {
            $mimeType    = $body->getMimeType();
            $disposition = $body->getDisposition();
            $encoding    = $body->getEncoding();
            $filename    = $this->encodedFileName($body->getFilename());
            $body        = $body->getContent();
        }

        $attach = new \Zend\Mime\Part($body);
        $attach->setType($mimeType);
        $attach->setDisposition($disposition);
        $attach->setEncoding($encoding);
        $attach->setFileName($filename);

        $this->attachments[] = $attach;
        return $this;
    }

    /**
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected function prepareMessage()
    {
        parent::prepareMessage();

        if (!count($this->attachments) || $this->hasBuiltInAttachmentFunction()) {
            return $this;
        }
        if ($this->message instanceof \Ebizmarts\Mandrill\Model\Message) {
            /** @var \Zend\Mime\Part $attachment */
            foreach ($this->attachments as $attachment) {
                $this->message->createAttachment(
                    base64_decode($attachment->getContent()),
                    $attachment->getType(),
                    $attachment->getDescription(),
                    $attachment->getEncoding(),
                    $attachment->getFileName()
                );
            }
        } else {
            $parts = $this->message->getBody()->getParts();
            $parts = array_merge($parts, $this->attachments);
            $body  = new \Zend\Mime\Message();
            $body->setParts($parts);
            $this->message->setBody($body);
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function hasBuiltInAttachmentFunction()
    {
        return version_compare($this->productMetadata->getVersion(), "2.2.8", "<");
    }
}
