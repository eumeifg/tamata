<?php
namespace MDC\ProvinceCenter\Api\Data;


interface AddressTypeDataInterface
{
	const COLUMN_NAME = 'address_type';

     /**
     * Get Is together or not
     *
     * @return string|int
     */
    public function getAddressType();

    /**
     * Set Is together or not
     * @param string|int $addressType
     * @return $this
     */
    public function setAddressType($addressType);
}