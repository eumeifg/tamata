<?php

namespace CAT\Address\Model\ResourceModel;

use CAT\Address\Api\Data\RomCityInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class RomCity extends AbstractDb
{
    /**
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        string $connectionName = null
    ) {
        parent::__construct(
            $context,
            $connectionName
        );
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('address_city', RomCityInterface::ENTITY_ID);
    }
}
