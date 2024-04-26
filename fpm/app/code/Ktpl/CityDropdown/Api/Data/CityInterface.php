<?php

namespace Ktpl\CityDropdown\Api\Data;

interface CityInterface
{
    const ENTITY_ID         = 'entity_id';
    const COUNTRY_ID      = 'country_id';
    const CITY_NAME         = 'city';

    /**
     * @return string
     */
    public function getEntityId();

    /**
     * @return string
     */
    public function getCityName();

    /**
     * @return string
     */
    public function getCountryId();

    /**
     * @param string $entityId
     * @return string
     */
    public function setEntityId($entityId);

    /**
     * @param string $cityName
     * @return string
     */
    public function setCityName($cityName);

    /**
     * @param string $countryId
     * @return string
     */
    public function setCountryId($countryId);
}
