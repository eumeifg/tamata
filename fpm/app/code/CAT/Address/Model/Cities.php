<?php

declare(strict_types=1);

namespace CAT\Address\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\SerializerInterface;

class Cities implements ConfigProviderInterface
{
    /** @var RomCityRepository  */
    private $romCityRepository;

    /** @var SearchCriteriaBuilder  */
    private $searchCriteria;

    /** @var SerializerInterface  */
    private $serializer;
    private $storeManager;

    public function __construct(
        RomCityRepository $romCityRepository,
        SearchCriteriaBuilder $searchCriteria,
        SerializerInterface $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->romCityRepository = $romCityRepository;
        $this->searchCriteria = $searchCriteria;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager; 
    }

    public function getConfig(): array
    {
        return [
            'cities' => $this->getCities()
        ];
    }

    private function getCities(): string
    {
        $searchCriteriaBuilder = $this->searchCriteria;
        $searchCriteria = $searchCriteriaBuilder->create();

        $citiesList = $this->romCityRepository->getList($searchCriteria);
        $items = $citiesList->getItems();

        $return = [];

        /** @var RomCity $item */
        foreach ($items as $item) {
            if($this->storeManager->getStore()->getId() == 2){

                $return[$item->getRegionId()][] = $item->getCityAr();
            }
            else{
    
                $return[$item->getRegionId()][] = $item->getCityName();
            }
        }

        return $this->serializer->serialize($return);
    }
}
