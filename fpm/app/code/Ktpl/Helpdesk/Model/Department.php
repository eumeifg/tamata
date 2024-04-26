<?php

namespace Ktpl\Helpdesk\Model;

use \Ktpl\Helpdesk\Api\Data\DepartmentInterface;

class Department extends \Magento\Framework\Model\AbstractModel implements DepartmentInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDepartmentId()
    {
        return $this->getData(self::KEY_DEPARTMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setDepartmentId($departmentId)
    {
        return $this->setData(self::KEY_DEPARTMENT_ID, $departmentId);
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
}