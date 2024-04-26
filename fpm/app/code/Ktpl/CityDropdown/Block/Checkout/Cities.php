<?php

namespace Ktpl\CityDropdown\Block\Checkout;

use Ktpl\CityDropdown\Model\CityRepository;
use Ktpl\CityDropdown\Model\City;
use Ktpl\CityDropdown\Model\ResourceModel\Collection\Collection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Template;
use Magento\Directory\Helper\Data;

/**
 * Class Cities
 * @package Ktpl\CityDropdown\Block\Checkout
 */
class Cities extends Template
{
    private $helperData;

    private $cityRepository;

    private $cityModel;

    private $collection;

    private $searchCriteria;

    public function __construct(
        Template\Context $context,
        Data $helperData,
        City $cityModel,
        Collection $collection,
        CityRepository $cityRepository,
        SearchCriteriaBuilder $searchCriteria,
        array $data = []
    ) {
        $this->cityModel = $cityModel;
        $this->searchCriteria = $searchCriteria;
        $this->collection = $collection;
        $this->helperData   = $helperData;
        $this->cityRepository = $cityRepository;
        parent::__construct($context, $data);
    }

    public function citiesJson()
    {
        $countriesJson = $this->helperData->getRegionJson();
        $countriesArray = json_decode($countriesJson, true);

        $searchCriteriaBuilder = $this->searchCriteria;
        $searchCriteria = $searchCriteriaBuilder->create();

        $citiesList = $this->cityRepository->getList($searchCriteria);
        $items = $citiesList->getItems();

        /** @var city $item */
        foreach ($items as $item) {
            $citiesData[$item->getEntityId()] = $item;
        }

        $countriesArrayUpdated = [];
        foreach ($citiesData as $cityId => $cityData) {
            $id       = $cityData->getId();
            $cityName = $cityData->getCityName();
            $region['city'][$cityName] = [
                'name' => $cityName,
                'id' => $cityId
            ];
        }

        $countriesJsonUpdated = json_encode($region);
        return $countriesJsonUpdated;
    }
}