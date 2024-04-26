<?php

namespace Ktpl\Tookan\Ui\Component\Listing\Column;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magedelight\Vendor\Api\VendorRepositoryInterface;

class VendorMobile extends Column
{
    protected $_shipmentRepository;
    /**
     * @var VendorRepositoryInterface
     */
    private $vendorRepository;

    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, ShipmentRepositoryInterface $shipmentRepository, VendorRepositoryInterface $vendorRepository, array $components = [], array $data = [])
    {
        $this->_shipmentRepository = $shipmentRepository;
        $this->vendorRepository = $vendorRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $vendor = $this->vendorRepository->getById($item["vendor_id"]);
                $item[$this->getData('name')] = $vendor->getMobile();
            }
        }

        return $dataSource;
    }
}
