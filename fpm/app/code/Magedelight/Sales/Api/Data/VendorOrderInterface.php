<?php

namespace Magedelight\Sales\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface VendorOrderInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const VENDOR_ORDER_ID = 'vendor_order_id';

    const ORDER_ID = 'order_id';

    const VENDOR_ID = 'vendor_id';

    const INCREMENT_ID = 'increment_id';

    const STATUS = 'status';

    const STATUS_LABEL = 'status_label';

    const TOTAL_REFUNDED = 'total_refunded';

    const GRAND_TOTAL = 'grand_total';

    const SUBTOTAL = 'subtotal';

    const CREATED_AT = 'created_at';

    const CUSTOMER_FIRSTNAME = 'firstname';

    const CUSTOMER_LASTNAME = 'lastname';

    const BILL_TO_NAME = 'bill_to_name';

    const SHIP_TO_NAME = 'ship_to_name';

    const IS_CUSTOMER_REVIEW_EXISTS = 'is_customer_review_exists';

    const IS_CONFIRMED = 'is_confirmed';

    const SHIPPING_AMOUNT = 'shipping_amount';

    const SHIPPING_DESCRIPTION = 'shipping_description';

    const CAN_CONFIRM = 'can_confirm';

    const CAN_CANCEL = 'can_cancel';

    const CAN_SHIP = 'can_ship';

    const CAN_INVOICE = 'can_invoice';

    const CAN_GENERATE_PACKING_SLIP = 'can_generate_packing_slip';

    const CAN_MANIFEST = 'can_manifest';

    const CAN_PRINT_INVOICE = 'can_print_invoice';

    const MOVE_TO_INTRANSIT = 'move_to_intransit';

    const MOVE_TO_DELIVERED = 'move_to_delivered';

    /**
     * Get Vendor Order Id
     *
     * @return int
     */
    public function getVendorOrderId();

    /**
     * Set Vendor Order Id
     * @param int $vendorOrderId
     * @return $this
     */
    public function setVendorOrderId($vendorOrderId);

    /**
     * Gets main order Id.
     *
     * @return int|null Main Order ID.
     */
    public function getOrderId();

    /**
     * Sets main order ID.
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Get Vendor Id
     *
     * @return int
     */
    public function getVendorId();

    /**
     * Set Vendor Id
     * @param int $vendorId
     * @return $this
     */
    public function setVendorId($vendorId);

    /**
     * Gets the subtotal for the order.
     *
     * @return float|null Subtotal.
     */
    public function getSubtotal();

    /**
     * Sets the subtotal for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setSubtotal($amount);

    /**
     * Get Grand Total
     *
     * @return float|string
     */
    public function getGrandTotal();

    /**
     * Set Grand Total
     * @param float|string $grandTotal
     * @return $this
     */
    public function setGrandTotal($grandTotal);

    /**
     * Get Increment Id
     *
     * @return string
     */
    public function getIncrementId();

    /**
     * Set Increment Id
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);

    /**
     * Gets the shipping amount for the order.
     *
     * @return float|null Shipping amount.
     */
    public function getShippingAmount();

    /**
     * Sets the shipping amount for the order.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingAmount($amount);

    /**
     * Gets the shipping description for the order.
     *
     * @return string|null Shipping description.
     */
    public function getShippingDescription();

    /**
     * Sets the shipping description for the order.
     *
     * @param string $description
     * @return $this
     */
    public function setShippingDescription($description);

    /**
     * Get label of order status
     *
     * @return string
     */
    public function getStatusLabel();

    /**
     * Set label of order status
     *
     * @param string $statusLabel
     * @return $this
     */
    public function setStatusLabel($statusLabel);

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set Status
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get Is Confirmed
     *
     * @return string|int
     */
    public function getIsConfirmed();

    /**
     * Set Is Confirmed
     * @param string|int $isConfirmed
     * @return $this
     */
    public function setIsConfirmed($isConfirmed);

    /**
     * Get Total Refunded
     *
     * @return float
     */
    public function getTotalRefunded();

    /**
     * Set Status
     * @param float $totalRefunded
     * @return $this
     */
    public function setTotalRefunded($totalRefunded);

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set Created At
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get First Name
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Set First Name
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName);

    /**
     * Get Last Name
     *
     * @return string
     */
    public function getLastName();

    /**
     * Set Last Name
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName);

    /**
     * Get Bill To Name
     *
     * @return string
     */
    public function getBillToName();

    /**
     * Set Bill To Name
     * @param string $billToName
     * @return $this
     */
    public function setBillToName($billToName);

    /**
     * Get Ship To Name
     *
     * @return string
     */
    public function getShipToName();

    /**
     * Set Ship To Name
     * @param string $shipToName
     * @return $this
     */
    public function setShipToName($shipToName);

    /**
     * Get flag if customer has given review to vendor
     *
     * @return bool
     */
    public function getIsCustomerReviewExists();

    /**
     * Get flag for order confirmation
     *
     * @return bool
     */
    public function getCanConfirm();

    /**
     * Get flag for order cancellation
     *
     * @return bool
     */
    public function getCanCancel();

    /**
     * Get flag for shipment generation
     *
     * @return bool
     */
    public function getCanShip();

    /**
     * Get flag for invoice generation
     *
     * @return bool
     */
    public function getCanInvoice();

    /**
     * Get flag for manifest generation
     *
     * @return bool
     */
    public function getCanManifest();

    /**
     * Get flag for packing slip generation
     *
     * @return bool
     */
    public function getCanGeneratePackingSlip();

    /**
     * Get flag for invoice download/print
     *
     * @return bool
     */
    public function getCanPrintInvoice();

    /**
     * Get flag to change order status to Intransit.
     *
     * @return bool
     */
    public function getMoveToIntransit();

    /**
     * Get flag to change order status to Delivered.
     *
     * @return bool
     */
    public function getMoveToDelivered();

    /**
     * Set flag to check if customer has given review to vendor
     * @param string $flag
     * @return $this
     */
    public function setIsCustomerReviewExists($flag);

    /**
     * Gets items for the order.
     *
     * @return \Magedelight\Sales\Api\Data\OrderItemInterface[] Array of items.
     */
    public function getItems();

    /**
     * Sets items for the order.
     *
     * @param \Magedelight\Sales\Api\Data\OrderItemInterface[] $items
     * @return $this
     */
    public function setItems($items);

    /**
     * Gets order payment
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface|null
     */
    public function getPayment();

    /**
     * Sets order payment
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface|null $payment
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface
     */
    public function setPayment(\Magento\Sales\Api\Data\OrderPaymentInterface $payment = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Sales\Api\Data\VendorOrderExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Sales\Api\Data\VendorOrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Sales\Api\Data\VendorOrderExtensionInterface $extensionAttributes
    );
}
