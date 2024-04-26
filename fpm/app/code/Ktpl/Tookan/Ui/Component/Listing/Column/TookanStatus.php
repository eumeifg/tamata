<?php

namespace Ktpl\Tookan\Ui\Component\Listing\Column;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Ktpl\Tookan\Model\Config\Source\TookanStatus as TookanStatusSource;

class TookanStatus extends Column
{
    protected $_shipmentRepository;
    protected $_searchCriteria;
    /**
     * @var TookanStatusSource
     */
    private $tookanStatus;

    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, ShipmentRepositoryInterface $shipmentRepository, SearchCriteriaBuilder $criteria, TookanStatusSource $tookanStatus, array $components = [], array $data = [])
    {
        $this->_shipmentRepository = $shipmentRepository;
        $this->_searchCriteria = $criteria;
        $this->tookanStatus = $tookanStatus;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $order = $this->_shipmentRepository->get($item["entity_id"]);
                $status = $order->getData("tookan_status");

                $tookanStatuses = $this->tookanStatus->toArray();
                $tookanStatus = $tookanStatuses[$status];

                // $this->getData('name') returns the name of the column so in this case it would return export_status
                $item[$this->getData('name')] = $tookanStatus;
            }
        }

        return $dataSource;
    }
}
