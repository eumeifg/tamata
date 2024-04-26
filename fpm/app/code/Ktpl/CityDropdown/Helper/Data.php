<?php

namespace Ktpl\CityDropdown\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ktpl\CityDropdown\Model\CityRepository;
use Ktpl\CityDropdown\Model\City;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Handles the config and other settings
 *
 * Class Data
 * @package Eadesigndev\RomCity\Helper
 */
class Data extends AbstractHelper
{
    const NAME_FILE  = 'manage_city/custom_group/city_file_upload';

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    private $cityRepository;
    private $searchCriteria;

    /**
     * Data constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CityRepository $cityRepository,
        SearchCriteriaBuilder $searchCriteria
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        $this->searchCriteria = $searchCriteria;
        $this->cityRepository = $cityRepository;
        parent::__construct($context);
    }

    /**
     * @param string $configPath
     * @return bool
     */
    public function getScopeConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getConfigFileName($nameFile = self::NAME_FILE)
    {
        return $this->getScopeConfig($nameFile);
    }
    public function getCityData()
    {
        $searchCriteriaBuilder = $this->searchCriteria;
        $searchCriteria = $searchCriteriaBuilder->create();
        $citiesList = $this->cityRepository->getList($searchCriteria);
        $items = $citiesList->getItems();

        /** @var City $item */
        foreach ($items as $item) {
            $citiesData[$item->getEntityId()] = $item;
        }
        $cities = [];
            foreach ($citiesData as $cityId => $cityData) {
                $id       = $cityData->getId();
                $cityName = $cityData->getCityName();
                $cities[$id] = [
                    'name' => $cityName,
                    'id' => $cityId
                ];
            }
        return $cities;
    }

}