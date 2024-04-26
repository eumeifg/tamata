<?php

namespace Ktpl\Helpdesk\Api\Data;

interface CreateTicketInterface
{
    const KEY_PRIORITY = 'priority';

    const KEY_DEPARTMENT = 'department';

    const KEY_ORDER = 'order';

    const KEY_CUSTOMER_ID = 'customer_id';

    /**
     * @return \Ktpl\Helpdesk\Api\Data\PriorityInterface[]
     */
    public function getPriority();

    /**
     * @param \Ktpl\Helpdesk\Api\Data\PriorityInterface[] $priority
     * @return $this
     */
    public function setPriority($priority);

    /**
     * @return \Ktpl\Helpdesk\Api\Data\DepartmentInterface[]
     */
    public function getDepartment();

    /**
     * @param \Ktpl\Helpdesk\Api\Data\DepartmentInterface[] $department
     * @return $this
     */
    public function setDepartment($department);

    /**
     * @return \Ktpl\Helpdesk\Api\Data\OrderDataInterface[]
     */
    public function getOrder();

    /**
     * @param \Ktpl\Helpdesk\Api\Data\OrderDataInterface[] $order
     * @return $this
     */
    public function setOrder($order);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param $customerId
     * @return $this
     */
    public function setCustomerId($customerId);
}