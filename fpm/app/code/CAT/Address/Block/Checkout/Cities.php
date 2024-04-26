<?php

namespace CAT\Address\Block\Checkout;

use CAT\Address\Model\RomCityRepository;
use CAT\Address\Model\RomCity;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;

class Cities extends Template
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
     * @param Template\Context $context
     * @param RomCityRepository $romCityRepository
     * @param SearchCriteriaBuilder $searchCriteria
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
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
            if($this->_storeManager->getStore()->getId() == 2){
                $return[] = ['region_id' => $item->getRegionId(), 'city_name' => $item->getCityAr()];
            }else{
                $return[] = ['region_id' => $item->getRegionId(), 'city_name' => $item->getCityName()];
            }
        }

        return $this->serializer->serialize($return);
    }
}
