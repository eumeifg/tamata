<?php

namespace CAT\VIP\Cron;

use CAT\VIP\Helper\Data;
use CAT\VIP\Model\VipCustomer;

class Unassignvip
{
    /**
     * @var ProductSkusUpdate
     */
    protected $_vendorHelper;
    protected $vipCustomer;

    /**
     * @param ProductSkusUpdate $productSkusUpdate
     */
    public function __construct(
        Data $Data,
        VipCustomer $vipCustomer
    ) {
        $this->_vendorHelper = $Data;
        $this->vipCustomer = $vipCustomer;
    }

    /**
     * @return void
     */
    public function unassignvip() {
        $this->vipCustomer->getVipgetlastmonthorders();
    }
}
