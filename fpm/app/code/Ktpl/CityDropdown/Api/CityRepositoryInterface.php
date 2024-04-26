<?php

namespace Ktpl\CityDropdown\Api;

use Ktpl\CityDropdown\Api\Data\CityInterface;

interface CityRepositoryInterface
{
    /**
     * @param CityInterface $templates
     * @return mixed
     */
    public function save(CityInterface $templates);

    /**
     * @param string $countryId
     * @return mixed
     */
    public function getCityByCountryId($countryId);

}