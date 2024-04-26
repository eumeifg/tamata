<?php

namespace Ktpl\Helpdesk\Api\Data;

interface PriorityInterface
{
    const KEY_PRIORITY_ID = 'priority_id';
    const KEY_PRIORITY_VALUE = 'priority_value';

    /**
     * @return string
     */
    public function getPriorityId();

    /**
     * @param int $priorityId
     * @return $this
     */
    public function setPriorityId($priorityId);

    /**
     * @return string
     */
    public function getPriorityValue();

    /**
     * @param string $priorityVal
     * @return $this
     */
    public function setPriorityValue($priorityVal);
}