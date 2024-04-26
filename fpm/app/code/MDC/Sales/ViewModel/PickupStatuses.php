<?php

namespace MDC\Sales\ViewModel;

class PickupStatuses implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * @var \MDC\Sales\Model\Source\Order\PickupStatus
     */
    protected $pickupStatuses;

    /**
     * PickupStatuses constructor.
     * @param \MDC\Sales\Model\Source\Order\PickupStatus $pickupStatuses
     */
    public function __construct(
        \MDC\Sales\Model\Source\Order\PickupStatus $pickupStatuses
    ) {
       $this->pickupStatuses = $pickupStatuses;
    }

    /**
     *
     * @param string $pickupStatus
     * @return string
     */
    public function getPickupStatus($pickupStatus)
    {
        return $this->pickupStatuses->getOptionText($pickupStatus);
    }
}
