<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Mobile_Connector
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\MobileInit\Api\Data;

/**
 * @api
 */
interface MobileConfigModuleInterface
{

    /**
     * @param string $language
     * @return $this
     */
    public function setModuleName($moduleName);

   /**
    * @return string.
    */
    public function getModuleName();

   /**
    * @param bool $moduleStatus
    * @return $this
    */
    public function setModuleStatus($moduleStatus);

   /**
    * @return bool
    */
    public function getModuleStatus();
}
