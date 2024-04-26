<?php

namespace CAT\Address\Block\Checkout;

use Magento\Backend\Block\Template;
use CAT\Address\Model\RomCityRepository;
use CAT\Address\Model\RomCity;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Backend\Block\Template\Context;

class CityUpdater extends Template
{
     /**
      * @var RomCityRepository
      */
    private $romCityRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteria;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Context $context
     * @param RomCityRepository $romCityRepository
     * @param SearchCriteriaBuilder $searchCriteria
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        RomCityRepository $romCityRepository,
        SearchCriteriaBuilder $searchCriteria,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->searchCriteria = $searchCriteria;
        $this->romCityRepository = $romCityRepository;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|string
     */
    public function citiesJson()
    {
        $searchCriteriaBuilder = $this->searchCriteria;
        $searchCriteria = $searchCriteriaBuilder->create();

        $citiesList = $this->romCityRepository->getList($searchCriteria);
        $items = $citiesList->getItems();

        $return = [];

        /** @var RomCity $item */
        foreach ($items as $item) {
            $return[] = ['region_id' => $item->getRegionId(), 'city_name' => $item->getCityName()];
        }

        return $this->serializer->serialize($return);
    }
}
