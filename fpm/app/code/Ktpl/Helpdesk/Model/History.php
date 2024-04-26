<?php

namespace Ktpl\Helpdesk\Model;

use \Ktpl\Helpdesk\Api\Data\HistoryInterface;

class History extends \Magento\Framework\Model\AbstractModel implements HistoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFrom()
    {
        return $this->getData(self::KEY_FROM);
    }

    /**
     * {@inheritdoc}
     */
    public function setFrom($from)
    {
        return $this->setData(self::KEY_FROM, $from);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->getData(self::KEY_MESSAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        return $this->setData(self::KEY_MESSAGE, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function getDepartmentName()
    {
        return $this->getData(self::KEY_DEPARTMENT_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setDepartmentName($departmentName)
    {
        return $this->setData(self::KEY_DEPARTMENT_NAME, $departmentName);
    }

    /**
     * {@inheritdoc}
     */
    public function getDate()
    {
        return $this->getData(self::KEY_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDate($date)
    {
        return $this->setData(self::KEY_DATE, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachments()
    {
        return $this->getData(self::KEY_ATTACHMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttachments($attachments)
    {
        return $this->setData(self::KEY_ATTACHMENT, $attachments);
    }
}