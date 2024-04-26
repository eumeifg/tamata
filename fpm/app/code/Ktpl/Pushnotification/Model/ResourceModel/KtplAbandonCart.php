<?php declare(strict_types=1);

namespace Ktpl\Pushnotification\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

class KtplAbandonCart extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

	public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }


    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ktpl_abandon_cart_list', 'id');
    }
}

