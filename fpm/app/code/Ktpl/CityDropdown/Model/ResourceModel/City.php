<?php

namespace Ktpl\CityDropdown\Model\ResourceModel;

use Ktpl\CityDropdown\Api\Data\CityInterface;
use Ktpl\CityDropdown\Setup\InstallSchema;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class City
 * @package Ktpl\CityDropdown\Model\ResourceModel
 */
class City extends AbstractDb
{
    /**
     * City constructor.
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

    public function _construct()
    {
        $this->_init(InstallSchema::TABLE, CityInterface::ENTITY_ID);
    }
}