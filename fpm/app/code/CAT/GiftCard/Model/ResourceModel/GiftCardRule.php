<?php

namespace CAT\GiftCard\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class GiftCardRule
 * @package CAT\GiftCard\Model\ResourceModel
 */
class GiftCardRule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * GiftCardRule constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('giftcard_rule', 'rule_id');
    }
}