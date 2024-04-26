<?php

namespace Ktpl\Tookan\Ui\Component\Listing\Column;

use Ktpl\Tookan\Helper\Data;
use Ktpl\Tookan\Model\Config\Source\TookanStatus;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class DeliveryLatitude extends Column
{
    protected $orderRepository;
    /**
     * @var Data
     */
    private $tookanHelper;

    /**
     * DeliveryLatitude constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param Data $tookanHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        Data $tookanHelper,
        array $components = [],
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->tookanHelper = $tookanHelper;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                /*if ($item["tookan_status"] == TookanStatus::READY_TO_SHIPPED) {
                    $item[$this->getData('name')] = $this->tookanHelper->getStoreLatitude();
                } else {*/
                    $order = $this->orderRepository->get($item["order_id"]);
                    $shippingAddress = $order->getShippingAddress();
                    $item[$this->getData('name')] = $shippingAddress->getLatitude();
                /*}*/
            }
        }

        return $dataSource;
    }
}
