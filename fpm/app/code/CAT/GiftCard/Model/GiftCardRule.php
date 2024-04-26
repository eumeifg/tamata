<?php

namespace CAT\GiftCard\Model;

class GiftCardRule extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\CAT\GiftCard\Model\ResourceModel\GiftCardRule::class);
        $this->setIdFieldName('rule_id');
    }
}