<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens;


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
            \Ktpl\Pushnotification\Model\KtplDevicetokens::class,
            \Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens::class
        );
    }
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
                ['customer_entity' => $this->getTable('customer_entity')],
                'main_table.customer_id = customer_entity.entity_id',
                [
                    'customer_email' => 'customer_entity.email',
                    'full_name' => new \Zend_Db_Expr("CONCAT(`customer_entity`.`firstname`, ' ',`customer_entity`.`lastname`)")
                ]
            );

        return $this;
    }
}

