<?php

namespace CAT\Patches\Model\ResourceModel\Indexer\Vendor;

use Magedelight\Catalog\Model\Config\Source\DefaultVendor\Criteria;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DB\Select;

class DefaultVendors extends \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendors
{

    protected function getRatingAvg()
    {
        if (!$this->ratingAvg) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(
                ['rvrt' => $this->getTable('md_vendor_rating_rating_type')],
                [
                      '(SUM(`rvrt`.`rating_avg`)/(SELECT count(*) FROM '
                      . $this->getTable('md_vendor_rating') . ' where main_table.vendor_id = '
                      . $this->getTable('md_vendor_rating') . '.vendor_id AND '
                      . $this->getTable('md_vendor_rating') . '.is_shared = 1)  / 20)'
                ]
            );
            $select->joinLeft(
                ['rvr' => $this->getTable('md_vendor_rating')],
                '`rvr`.`vendor_rating_id` = `rvrt`.`vendor_rating_id`',
                []
            );

            $select->where('`rvr`.`vendor_id` = `main_table`.`vendor_id` AND `rvr`.`is_shared` = 1');
            $this->ratingAvg = $select;
        }
        return $this->ratingAvg;
    }

}
