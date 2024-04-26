<?php

declare(strict_types=1);

namespace MDC\ProvinceCenter\Model\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use MDC\ProvinceCenter\Api\Data\AddressTypeDataInterface;
/**
 * 
 */
class AddressTypeData extends AbstractSimpleObject implements AddressTypeDataInterface
{
	
	 /**
     * {@inheritDoc}
     */
    public function getAddressType()
    {
        return $this->_get(self::COLUMN_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setAddressType($addressType)
    {
        return $this->setData(self::COLUMN_NAME, $addressType);
    }
}