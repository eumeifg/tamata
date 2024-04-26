<?php

namespace Ktpl\Helpdesk\Model;

use Ktpl\Helpdesk\Api\Data\AttachmentInterface;

class Attachment extends \Magento\Framework\Model\AbstractModel implements AttachmentInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getLink()
    {
        return $this->getData(self::KEY_LINK);
    }

    /**
     * {@inheritdoc}
     */
    public function setLink($link)
    {
        return $this->setData(self::KEY_LINK, $link);
    }
}