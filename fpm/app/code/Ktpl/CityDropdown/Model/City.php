<?php

namespace Ktpl\CityDropdown\Model;

use Ktpl\CityDropdown\Api\Data\CityInterface;
use Ktpl\CityDropdown\Model\ResourceModel\City as CityResourceModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource as AbstractResourceModel;
use Magento\Framework\Registry;

class City extends AbstractModel implements CityInterface
{
    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResourceModel $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        $this->_init(CityResourceModel::class);
    }

    public function getEntityId()
    {
        return $this->getData(CityInterface::ENTITY_ID);
    }

    public function getCityName()
    {
        return $this->getData(CityInterface::CITY_NAME);
    }

    public function getCountryId()
    {
        return $this->getData(CityInterface::COUNTRY_ID);
    }


    public function setEntityId($entityId)
    {
        $this->setData(CityInterface::ENTITY_ID, $entityId);
    }


    public function setCityName($cityName)
    {
        $this->setData(CityInterface::CITY_NAME, $cityName);
    }

    public function setCountryId($countryId)
    {
        return $this->setData(CityInterface::COUNTRY_ID, $countryId);
    }

}