<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace MDC\Sales\Plugin;

class AddPickupStatus
{

    /**
     * @var \MDC\Sales\Model\Source\Order\PickupStatus
     */
    protected $pickupStatuses;

    /**
     * AddPickupStatus constructor.
     * @param \MDC\Sales\Model\Source\Order\PickupStatus $pickupStatuses
     */
    public function __construct(
        \MDC\Sales\Model\Source\Order\PickupStatus $pickupStatuses
    ) {
        $this->pickupStatuses = $pickupStatuses;
    }

    /**
     * Used specifically in seller area/app.
     * Need to maintain the status as per vendor scope in seller area. Default is customer scope.
     * {@inheritDoc}
     */
    public function afterGetVendorOrderById
    (
        \Magedelight\Sales\Model\OrderRepository $subject,
        $result,
        $vendorOrderId
    ) {
        $extension = $result->getExtensionAttributes();
        $extension->setPickupStatus($result->getPickupStatus());
        $extension->setPickupStatusLabel($this->pickupStatuses->getOptionText($result->getPickupStatus()));
        $result->setExtensionAttributes($extension);
        return $result;
    }
}
