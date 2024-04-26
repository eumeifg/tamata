<?php

namespace Ktpl\Helpdesk\Api\Data;

interface HistoryInterface
{
    const KEY_FROM             = 'from';
    const KEY_MESSAGE          = 'message';
    const KEY_DEPARTMENT_NAME  = 'department_name';
    const KEY_DATE             = 'date';
    const KEY_ATTACHMENT  = 'attachments';

    /**
     * @return string
     */
    public function getFrom();

    /**
     * @param string $from
     * @return $this
     */
    public function setFrom($from);

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * @return string
     */
    public function getDepartmentName();

    /**
     * @param string $departmentName
     * @return $this
     */
    public function setDepartmentName($departmentName);

    /**
     * @return string
     */
    public function getDate();

    /**
     * @param string $date
     * @return $this
     */
    public function setDate($date);

    /**
     * @return \Ktpl\Helpdesk\Api\Data\AttachmentInterface[]
     */
    public function getAttachments();

    /**
     * @param \Ktpl\Helpdesk\Api\Data\AttachmentInterface[] $attachments
     * @return $this
     */
    public function setAttachments($attachments);
}