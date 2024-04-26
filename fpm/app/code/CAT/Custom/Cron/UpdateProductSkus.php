<?php

namespace CAT\Custom\Cron;

use CAT\Custom\Model\Entity\ProductSkusUpdate;

class UpdateProductSkus
{
    /**
     * @var ProductSkusUpdate
     */
    protected $_productSkusUpdate;

    /**
     * @param ProductSkusUpdate $productSkusUpdate
     */
    public function __construct(
        ProductSkusUpdate $productSkusUpdate
    ) {
        $this->_productSkusUpdate = $productSkusUpdate;
    }

    /**
     * @return void
     */
    public function updateProductSkus() {
        $this->_productSkusUpdate->productSkusUpdate();
    }
}
