<?php

declare (strict_types = 1);

namespace MDC\PickupPoints\Api\Data;

interface PickupPointsObjInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const KEY_DATA = "pickuppoints_list";

    const PICKUPPOINT_ID = "pickuppoint_id";
    const PICKUPPOINT_STREET = 'street';
    const PICKUPPOINT_CITY = 'city';
    const PICKUPPOINT_COUNTRY = 'country_id';
    const PICKUPPOINT_LATITUDE = 'latitude';
    const PICKUPPOINT_LONGITUDE = 'longitude';
 

    /**
     * Get pickup location id
     * @return string|bool
     */
    public function getPickuppointId();

    /**
     * Set pickup location id
     * @param string $id
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setPickuppointId($id);


    /**
     * get pickup country
     * @return string|bool
     */
    public function getCountryId();

    /**
     * Set pickup country
     * @param string $countryId
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setCountryId($countryId);

    /**
     * get pikcup point address location and street address line
     *
     * @return array
     */
    public function getStreet();

    /**
     * set pikcup point address location and street address line
     *
     * @param array $address
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setStreet(array $address);


    /**
     * get pickup city
     * @return string|bool
     */
    public function getCity();

    /**
     * Set pickup city
     * @param string $city
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setCity($city);

    /**
     * get pickup location latitude
     * @return string|bool
     */
    public function getLatitude();

    /**
     * Set pickup location latitude
     * @param string $latitude
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setLatitude($latitude);

    /**
     * get pickup location longitude
     * @return string|bool
     */
    public function getLongitude();

    /**
     * Set pickup location longitude
     * @param string $longitude
     * @return \MDC\PickupPoints\Api\Data\PickupPointsObjInterface
     */
    public function setLongitude($longitude);

}
