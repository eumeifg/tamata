<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Model\ResourceModel\Vendor\Grid;

class ApprovedCollection extends \Magedelight\Vendor\Model\ResourceModel\Vendor\Grid\ApprovedCollection
{
    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['rbul' => 'md_vendor_user_link'],
            'main_table.vendor_id = rbul.vendor_id',
            ['parent_id', 'parent_name' => new \Zend_Db_Expr("(Select business_name from md_vendor_website_data where vendor_id = parent_id)")]
        );
        $this->addFilterToMap('parent_name', new \Zend_Db_Expr("(Select business_name from md_vendor_website_data where vendor_id = rbul.parent_id)"));
        return $this;
    }
}
