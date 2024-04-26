<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model\ResourceModel\RatingTypes\Grid;

class Collection extends \Magento\Review\Model\ResourceModel\Rating\Grid\Collection
{

    public function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);
        return $this;
    }
}
