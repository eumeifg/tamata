<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Vendor\Api\Data;

/**
 * @api
 */
interface CategoryDataInterface
{

    /**
     * @param string $categoryLabel
     * @return $this
     */
    public function setCategoryLabel($categoryLabel);

   /**
    * @return string.
    */
    public function getCategoryLabel();

   /**
    * @param int $categoryId
    * @return $this
    */
    public function setCategoryId($categoryId);

   /**
    * @return int.
    */
    public function getCategoryId();

   /**
    * @param string $catContentType
    * @return $this
    */
    public function setContentType($catContentType);

   /**
    * @return string.
    */
    public function getContentType();

   /**
    * @param bool $isSelected
    * @return $this
    */
    public function setIsSelected($isSelected);

   /**
    * @return bool
    */
    public function getIsSelected();
   
   /**
    * @return $childrenData
    */
    public function getChildrenData();

    /**
     * @param  $childrenData
     * @return $this
     */
    public function setChildrenData(array $childrenData = null);

    /**
     * @param string|null $categoryIcon
     * @return $this
     */
    public function setCategoryIcon($categoryIcon);

   /**
    * @return string|null
    */
    public function getCategoryIcon();
}
