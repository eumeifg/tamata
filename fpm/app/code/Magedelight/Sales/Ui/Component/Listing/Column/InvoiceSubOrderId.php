<?php

namespace Magedelight\Sales\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class InvoiceSubOrderId extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $itemOrderId = $item['order_increment_id'];
                $item[$this->getData('name')] = $itemOrderId . '-' . $item['vendor_order_id'];
            }
        }
        return $dataSource;
    }
}
