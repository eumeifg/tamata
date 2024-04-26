<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @author Rocket Bazaar Core Team
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'vendor_order_id';
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'vendor_order_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'vendor_order_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Magedelight\Sales\Model\Order::class, \Magedelight\Sales\Model\ResourceModel\Order::class);
    }
}
