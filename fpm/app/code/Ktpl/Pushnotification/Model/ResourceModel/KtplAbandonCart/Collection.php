<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Model\ResourceModel\KtplAbandonCart;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Ktpl\Pushnotification\Model\KtplAbandonCart::class,
            \Ktpl\Pushnotification\Model\ResourceModel\KtplAbandonCart::class
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();
    }

    
}
