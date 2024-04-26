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
namespace Magedelight\Vendor\Model\ResourceModel;

class Rating extends \Magento\Review\Model\ResourceModel\Rating
{
    
    /**
     * Initialize unique fields
     * Validate rating title based on entity_id and rating code.
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [['field' => ['entity_id','rating_code'], 'title' => 'Rating Title']];
        return $this;
    }
}
