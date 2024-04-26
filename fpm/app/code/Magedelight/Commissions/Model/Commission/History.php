<?php

namespace Magedelight\Commissions\Model\Commission;

use Magento\Framework\Model\AbstractModel;

class History extends AbstractModel
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    
    protected function _construct()
    {

        $this->_init(\Magedelight\Commissions\Model\ResourceModel\Commission\History::class);
    }
}
