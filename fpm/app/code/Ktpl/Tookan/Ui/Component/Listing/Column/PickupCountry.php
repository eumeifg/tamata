<?php

namespace Ktpl\Tookan\Ui\Component\Listing\Column;

use Ktpl\Tookan\Helper\Data;
use Ktpl\Tookan\Model\Config\Source\TookanStatus;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magedelight\Vendor\Api\VendorRepositoryInterface;

class PickupCountry extends Column
{
    protected $_shipmentRepository;
    /**
     * @var VendorRepositoryInterface
     */
    private $vendorRepository;
    /**
     * @var Data
     */
    private $tookanHelper;
    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * PickupCity constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Data $tookanHelper
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param VendorRepositoryInterface $vendorRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Data $tookanHelper,
        ShipmentRepositoryInterface $shipmentRepository,
        VendorRepositoryInterface $vendorRepository,
        CountryFactory $countryFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_shipmentRepository = $shipmentRepository;
        $this->vendorRepository = $vendorRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->tookanHelper = $tookanHelper;
        $this->countryFactory = $countryFactory;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                /*if ($item["tookan_status"] == TookanStatus::READY_TO_DELIVER) {
                    $item[$this->getData('name')] = $this->tookanHelper->getStoreCountry();
                } else {*/
                    $vendor = $this->vendorRepository->getById($item["vendor_id"]);
                    $country = $this->countryFactory->create()->loadByCode($vendor->getPickupCountry());
                    $item[$this->getData('name')] = $country->getName();
                /*}*/
            }
        }

        return $dataSource;
    }
}
