<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\BannerManagement\Model\ResourceModel\Banner;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'banner_id';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ktpl\BannerManagement\Model\Banner', 'Ktpl\BannerManagement\Model\ResourceModel\Banner');
    }
}
