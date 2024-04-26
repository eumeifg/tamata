<?php
 
namespace Magedelight\Commissions\Model\ResourceModel\Commission;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class History extends AbstractDb
{
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    
    protected function _construct()
    {
        $this->_init('md_vendor_payment_history', 'payment_transaction_id');
    }
}
