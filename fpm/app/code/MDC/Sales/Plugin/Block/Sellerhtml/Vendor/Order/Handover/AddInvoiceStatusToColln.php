<?php

namespace MDC\Sales\Plugin\Block\Sellerhtml\Vendor\Order\Handover;

use Magedelight\Sales\Model\Order as VendorOrder;

class AddInvoiceStatusToColln extends \Magedelight\Sales\Block\Sellerhtml\Vendor\Order
{

    /**
     * @param array $includeStatuses
     * @param bool $skipfilterSearch
     * @param int $vendorId
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function beforeGetHandoverOrders(
        \Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Handover $subject,
        $includeStatuses = [VendorOrder::STATUS_SHIPPED],
        $skipfilterSearch = false,
        $vendorId = 0
    ) {
        $includeStatuses = [VendorOrder::STATUS_SHIPPED, VendorOrder::STATUS_PACKED];
        return [$includeStatuses, $skipfilterSearch, $vendorId];
    }
}
