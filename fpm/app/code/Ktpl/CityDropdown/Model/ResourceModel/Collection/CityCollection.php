<?php

namespace Ktpl\CityDropdown\Model\ResourceModel\Collection;
use Magento\Framework\Option\ArrayInterface;
use Ktpl\CityDropdown\Model\CityRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;


class CityCollection implements ArrayInterface
{
    private $cityRepository;
    private $searchCriteria;

    public function __construct(
        CityRepository $cityRepository,
        SearchCriteriaBuilder $searchCriteria,
        array $data = []
    ) {
        $this->cityRepository = $cityRepository;
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $searchCriteriaBuilder = $this->searchCriteria;
        $searchCriteria = $searchCriteriaBuilder->create();

        $citiesList = $this->cityRepository->getList($searchCriteria);
        $items = $citiesList->getItems();

        /** @var city $item */
        foreach ($items as $item) {
            $citiesData[$item->getEntityId()] = $item;
        }

        foreach ($citiesData as $cityId => $cityData) {
            $id       = $cityData->getId();
            $cityName = $cityData->getCityName();
            $region[] = [
                'label' => $cityName,
                'value' => $cityName
            ];
        }

        return $region;
    }

}
