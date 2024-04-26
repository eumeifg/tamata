<?php

namespace CAT\Custom\Cron;

use CAT\Custom\Model\Entity\VendorQtyUpdate;

class UpdateVendorQty
{
    /**
     * @var ProductSkusUpdate
     */
    protected $_VendorQtyUpdate;

    /**
     * @param ProductSkusUpdate $productSkusUpdate
     */
    public function __construct(
        VendorQtyUpdate $VendorQtyUpdate
    ) {
        $this->_VendorQtyUpdate = $VendorQtyUpdate;
    }

    /**
     * @return void
     */
    public function updateVendorQty() {
        $this->_VendorQtyUpdate->vendorQtyUpdate();
    }
}
