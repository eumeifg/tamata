<?php

namespace MDC\Sales\Api;

interface OrderStatusManagerInterface
{
    /**
     * @param mixed $vendorOrderIds
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     */
    public function vendorBulkOrderConfirm($vendorOrderIds);

    /**
     * @param mixed $vendorOrderIds
     * @param mixed $isUnlist
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     */
    public function vendorBulkOrderCancel($vendorOrderIds, $isUnlist);
}