<?php

declare (strict_types = 1);

namespace MDC\PickupPoints\Model\Data;

use MDC\PickupPoints\Api\Data\PickupPointsObjInterface;

class PickupPointsObj extends \Magento\Framework\Api\AbstractExtensibleObject implements PickupPointsObjInterface
{
    /**
     * Get pickup location id
     * @return string|bool
     */
    public function getPickuppointId()
    {       
        return $this->_get(self::PICKUPPOINT_ID);
    }

    /**
     * Set pickup location id
     * @param string $id
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setPickuppointId($id)
    {
        return $this->setData(self::PICKUPPOINT_ID, $id);
    }

    /**
     * get pickup country
     * @return string|bool
     */
    public function getCountryId()
    {
        return $this->_get(self::PICKUPPOINT_COUNTRY);
    }

    /**
     * Set pickup country
     * @param string $countryId
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::PICKUPPOINT_COUNTRY, $countryId);
    }

    /**
    * get pikcup point address location and street address line
     * @return string|bool
     */
    public function getStreet()
    {
       return $this->_get(self::PICKUPPOINT_STREET);
    }

    /**
    * set pikcup point address location and street address line
     *
     * @param array $address
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setStreet($address)
    {
        return $this->setData(self::PICKUPPOINT_STREET, $address);
    }

    /**
      * get pickup city
     * @return string|bool
     */
    public function getCity()
    {
        return $this->_get(self::PICKUPPOINT_CITY);
    }

    /**
     * Set pickup city
     * @param string $city
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setCity($city)
    {
        return $this->setData(self::PICKUPPOINT_CITY, $city);
    }

    /**
    * get pickup location latitude
     * @return string|bool
     */
    public function getLatitude()
    {
        return $this->_get(self::PICKUPPOINT_LATITUDE);
    }

    /**
      * Set pickup location latitude
     * @param string $latitude
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setLatitude($latitude)
    {
        return $this->setData(self::PICKUPPOINT_LATITUDE, $latitude);
    }


     /**
    * get pickup location longitude
     * @return string|bool
     */
    public function getLongitude()
    {
        return $this->_get(self::PICKUPPOINT_LONGITUDE);
    }

    /**
      * Set pickup location longitude
     * @param string $longitude
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setLongitude($longitude)
    {
        return $this->setData(self::PICKUPPOINT_LONGITUDE, $longitude);
    }
}
