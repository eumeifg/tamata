<?php

namespace CAT\GiftCard\Model\ResourceModel\GiftCardRule;

/**
 * Class Collection
 * @package CAT\GiftCard\Model\ResourceModel\GiftCardRule
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'rule_id';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('CAT\GiftCard\Model\GiftCardRule', 'CAT\GiftCard\Model\ResourceModel\GiftCardRule');
    }
}