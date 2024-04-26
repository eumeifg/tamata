<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\BannerManagement\Model\ResourceModel\Slider;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'slider_id';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ktpl\BannerManagement\Model\Slider', 'Ktpl\BannerManagement\Model\ResourceModel\Slider');
    }
}
