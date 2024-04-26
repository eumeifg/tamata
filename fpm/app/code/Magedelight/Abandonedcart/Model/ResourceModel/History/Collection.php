<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Model\ResourceModel\History;

use Magedelight\Abandonedcart\Model\History as Historymodal;
use Magedelight\Abandonedcart\Model\ResourceModel\History as Historyresource;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'history_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Historymodal::class, Historyresource::class);
    }
}
