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
interface MobileSettingDataInterface
{

   /**
    * @param int $id
    * @return $this
    */
    public function setId($id);

   /**
    * @return int
    */
    public function getId();

   /**
    * @param string $label
    * @return $this
    */
    public function setLabel($label);

   /**
    * @return string
    */
    public function getLabel();

   /**
    * @param string $code
    * @return $this
    */
    public function setCode($code);

   /**
    * @return string
    */
    public function getCode();

   /**
    * @param bool $isSelected
    * @return $this
    */
    public function setIsSelected($isSelected);

   /**
    * @return bool
    */
    public function getIsSelected();
}
