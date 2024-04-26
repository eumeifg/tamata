<?php

namespace Magedelight\Commissions\Model\ResourceModel\Commission\History;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'payment_transaction_id';

    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Magedelight\Commissions\Model\Commission\History::class,
            \Magedelight\Commissions\Model\ResourceModel\Commission\History::class
        );
    }
}
