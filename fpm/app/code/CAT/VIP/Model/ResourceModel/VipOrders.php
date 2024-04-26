<?php

namespace CAT\VIP\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class VipOrders extends AbstractDb {

protected function _construct() {
	$this->_init('cat_viporders', 'entity_id');
	}
}