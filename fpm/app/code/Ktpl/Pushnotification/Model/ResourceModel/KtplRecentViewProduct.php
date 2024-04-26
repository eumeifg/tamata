<?php declare(strict_types=1);

namespace Ktpl\Pushnotification\Model\ResourceModel;

class KtplRecentViewProduct extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ktpl_recent_view_product_list', 'id');
    }
}

