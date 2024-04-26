<?php

namespace MDC\Sales\Plugin\Model\Order;

class Listing extends \Magedelight\Sales\Model\Order\Listing
{
    public function getSubOrdersCollection($vendorId)
    {
        $collection = $this->vendorOrderCollectionFactory->create()
            ->addFieldToSelect(
                [
                    "rvo_vendor_order_id" => "vendor_order_id",
                    "rvo_vendor_id" => "vendor_id",
                    'rvo_increment_id' => "increment_id",
                    "status", "total_refunded",
                    'rvo_grand_total' => "grand_total",
                    "rvo_created_at" => "created_at",
                    "order_currency_code",
                    "vendor_id",
                    "order_id",
                    "cancelled_by",
                    "vendor_order_with_classification"
                ]
            )->addFieldToFilter(
                'main_table.vendor_id',
                $vendorId
            );
        return $collection;
    }
}