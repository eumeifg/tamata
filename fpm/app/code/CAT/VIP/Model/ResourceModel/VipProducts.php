<?php

namespace CAT\VIP\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class VIPProducts extends AbstractDb {

protected function _construct() {
	$this->_init('cat_vipproducts', 'entity_id');
	}
}