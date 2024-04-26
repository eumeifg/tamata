<?php

namespace Ktpl\Tookan\Ui\Component\Listing\Column;

use Ktpl\Tookan\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class DeliveryCountry extends Column
{
    protected $orderRepository;
    /**
     * @var Data
     */
    private $tookanHelper;
    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * DeliveryCountry constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param CountryFactory $countryFactory
     * @param Data $tookanHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        CountryFactory $countryFactory,
        Data $tookanHelper,
        array $components = [],
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->tookanHelper = $tookanHelper;
        $this->countryFactory = $countryFactory;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                /*if ($item["tookan_status"] == TookanStatus::READY_TO_SHIPPED) {
                    $item[$this->getData('name')] = $this->tookanHelper->getStoreCountry();
                } else {*/
                    $order = $this->orderRepository->get($item["order_id"]);
                    $shippingAddress = $order->getShippingAddress();
                    $country = $this->countryFactory->create()->loadByCode($shippingAddress->getCountryId());
                    $item[$this->getData('name')] = $country->getName();
                /*}*/
            }
        }

        return $dataSource;
    }
}
