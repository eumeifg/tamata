<?php

namespace CAT\Address\Api\Data;

interface RomCityInterface
{
    public const ENTITY_ID = 'entity_id';
    public const REGION_ID = 'region_id';
    public const CITY_NAME = 'city';
    public const CITY_ARABIC_NAME = 'city_ar';


    /**
     * @return mixed
     */
    public function getEntityId();

    /**
     * @return mixed
     */
    public function getRegionId();

    /**
     * @return mixed
     */
    public function getCityName();

    /**
     * @param $entityId
     * @return mixed
     */
    public function setEntityId($entityId);

    /**
     * @param $regionId
     * @return mixed
     */
    public function setRegionId($regionId);

    /**
     * @param $cityName
     * @return mixed
     */
    public function setCityName($cityName);

    /**
     * @param $cityArabicName
     * @return mixed
     */
    public function setCityArabicName($cityArabicName);
}
