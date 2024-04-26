<?php

namespace Ktpl\Helpdesk\Api\Data;

interface DepartmentInterface
{
    const KEY_DEPARTMENT_ID = 'department_id';
    const KEY_DEPARTMENT_NAME = 'department_name';

    /**
     * @return string
     */
    public function getDepartmentId();

    /**
     * @param int $departmentId
     * @return $this
     */
    public function setDepartmentId($departmentId);

    /**
     * @return string
     */
    public function getDepartmentName();

    /**
     * @param string $departmentName
     * @return $this
     */
    public function setDepartmentName($departmentName);
}