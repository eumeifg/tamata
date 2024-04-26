<?php
/**
 * php version 7.2.17
 */

namespace Ktpl\NavLinks\Api;


interface NavLinksManagementInterface
{

    /**
     * GET for Post api
     * @param int $param
     * @return string
     */
    public function getMenuItems($param);
}
