<?php

declare(strict_types=1);


namespace Ktpl\Pushnotification\Model\ResourceModel;


class KtplPushnotifications extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ktpl_pushnotification', 'id');
    }
}

