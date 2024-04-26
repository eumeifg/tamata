<?php


namespace CAT\VIP\Model\ResourceModel\VIPProducts;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {

	protected function _construct() {

		$this->_init('CAT\VIP\Model\VIPProducts', 'CAT\VIP\Model\ResourceModel\VIPProducts');

	}

}