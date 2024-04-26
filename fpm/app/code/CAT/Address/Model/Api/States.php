<?php

namespace CAT\Address\Model\Api;
use Psr\Log\LoggerInterface;
use Magento\Directory\Model\Country;
use CAT\Address\Model\ResourceModel\Collection\Collection;

class States
{
    protected $logger;
    protected $_country;
    protected $iraqCityRepository;
    protected $_storeManager;
    protected $_localeResolver;
    public function __construct(
        LoggerInterface $logger,
        Collection $iraqCityRepository,
        Country $country,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    )
    {
        $this->logger = $logger;
        $this->_country = $country;
        $this->_storeManager = $storeManager;
        $this->iraqCityRepository = $iraqCityRepository;
        $this->_localeResolver = $localeResolver;
    }
    /**
     * @inheritdoc
     */
    public function getStates()
    {
        $this->_storeManager->setCurrentStore(2);
        $this->_localeResolver->setLocale('ar_SA');

        $response = ['success' => false];
        try {
            // get states
            $countryCode = 'IQ';
            $regionCollection = $this->_country->loadByCode($countryCode)->getRegions();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $reginsarray = [];
            foreach($regionCollection as $region){
                $reginarray = [];
                $reginarray['region_id'] = $region->getRegionId();
                $reginarray['country_id'] = $region->getCountryId();
                $reginarray['code'] = $region->getCode();
                $reginarray['name'] = $region->getDefaultName();
                $reginarray['name_ar'] = $region->getName();
                $collection = $objectManager->create('CAT\Address\Model\ResourceModel\Collection\Collection');
                $collection = $collection->addFieldToFilter('region_id',$reginarray['region_id'])->toArray();
                $reginarray['cities'] = $collection['items'];
                $reginsarray[] = $reginarray;
            }
            $response =$reginsarray;
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            $this->logger->info($e->getMessage());
        }
        return $response; 
   }
}