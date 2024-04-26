<?php

namespace CAT\Address\Api;

use CAT\Address\Api\Data\RomCityInterface;

interface RomCityRepositoryInterface
{
    /**
     * @param RomCityInterface $templates
     * @return mixed
     */
    public function save(RomCityInterface $templates);

    /**
     * @param $value
     * @return mixed
     */
    public function getById($value);

    /**
     * @param RomCityInterface $templates
     * @return mixed
     */
    public function delete(RomCityInterface $templates);

    /**
     * @param $value
     * @return mixed
     */
    public function deleteById($value);
}
