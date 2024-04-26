<?php


namespace CAT\VIP\Model\ResourceModel\VipOrders;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {

	protected function _construct() {

		$this->_init('CAT\VIP\Model\VipOrders', 'CAT\VIP\Model\ResourceModel\VipOrders');

	}

}