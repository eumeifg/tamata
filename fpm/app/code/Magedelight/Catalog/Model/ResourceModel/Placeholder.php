<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\ResourceModel;

class Placeholder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table.
     *
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('md_eav_attribute_placeholder', 'attribute_placeholder_id');
    }
}
