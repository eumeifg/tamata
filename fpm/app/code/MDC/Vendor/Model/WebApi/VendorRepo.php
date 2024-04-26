<?php

namespace MDC\Vendor\Model\WebApi;

use Magedelight\Vendor\Api\VendorRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Directory\Model\CountryFactory;

/**
 * Class VendorRepo
 * @package MDC\Vendor\Model\WebApi
 */
class VendorRepo implements \MDC\Vendor\Api\VendorRepoInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var CountryFactory
     */
    protected $_countryFactory;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param VendorRepositoryInterface $vendorRepository
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        VendorRepositoryInterface $vendorRepository,
        CountryFactory $countryFactory
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->vendorRepository = $vendorRepository;
        $this->_countryFactory = $countryFactory;
    }

    /**
     * @inheritDoc
     */
    public function getVendorList()
    {
        $items = $this->vendorRepository->getList($this->searchCriteriaBuilder->create(), true)->getItems();
        $response = [];
        foreach ($items as $item) {
            $address = '';
            $address.= !empty($item->getAddress1()) ? $item->getAddress1().', ' : '';
            $address.= !empty($item->getCity()) ? $item->getCity().', ' : '';
            $address.= !empty($item->getRegion()) ? $item->getRegion().', ' : '';
            $address.= !empty($item->getCountryId()) ? $this->getCountryName($item->getCountryId()) : '';
            $address.= !empty($item->getPincode()) ? ' - ' . $item->getPincode() : '';
            if ($item->getName()) {
                $response[$item->getId()] = [
                    'id'  => $item->getId(),
                    'name' => $item->getName(),
                    'mobile' => $item->getMobile(),
                    'address' => $address
                ];
            }
        }
        return $response;
    }

    public function getCountryName($countryCode){
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }
}
