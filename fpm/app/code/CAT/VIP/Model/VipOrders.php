<?php

namespace CAT\VIP\Model;

class VipOrders extends \Magento\Framework\Model\AbstractModel {

	protected function _construct() {
		$this->_init('CAT\VIP\Model\ResourceModel\VipOrders');
	}
}