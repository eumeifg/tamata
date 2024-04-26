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
namespace Magedelight\Sales\Model\Order;

/**
 * Vendor Order Listing model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Listing extends \Magento\Framework\DataObject
{
    const BILLING_ALIAS = 'billing_o_a';
    const SHIPPING_ALIAS = 'shipping_o_a';
    const BILL_TO_FIRST_NAME_FIELD = 'billing_o_a.firstname';
    const BILL_TO_LAST_NAME_FIELD = 'billing_o_a.lastname';
    const SHIP_TO_FIRST_NAME_FIELD = 'shipping_o_a.firstname';
    const SHIP_TO_LAST_NAME_FIELD = 'shipping_o_a.lastname';

    /**
     * @var ResourceModel\Order\CollectionFactory
     */
    protected $vendorOrderCollectionFactory;

    /**
     * Listing constructor.
     * @param \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory
     */
    public function __construct(
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory
    ) {
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getSubOrdersCollection($vendorId)
    {
        $collection = $this->vendorOrderCollectionFactory->create()
            ->addFieldToSelect(
                [
                    "rvo_vendor_order_id" => "vendor_order_id",
                    "rvo_vendor_id" => "vendor_id",
                    'rvo_increment_id' => "increment_id",
                    "status", "total_refunded",
                    'rvo_grand_total' => "grand_total",
                    "rvo_created_at" => "created_at",
                    "order_currency_code",
                    "vendor_id",
                    "order_id",
                    "cancelled_by"
                ]
            )->addFieldToFilter(
                'main_table.vendor_id',
                $vendorId
            );
        return $collection;
    }

    /**
     * @param $collection
     */
    public function joinAddressColumnsToCollection($collection)
    {
        $joinTable = $collection->getTable('sales_order_address');
        $collection->getSelect()->joinLeft(
            [self::BILLING_ALIAS => $joinTable],
            "(main_table.order_id = " . self::BILLING_ALIAS . ".parent_id" .
            " AND " . self::BILLING_ALIAS . ".address_type = 'billing')",
            [
                self::BILL_TO_FIRST_NAME_FIELD,
                self::BILL_TO_LAST_NAME_FIELD
            ]
        )->joinLeft(
            [self::SHIPPING_ALIAS => $joinTable],
            "(main_table.order_id = " . self::SHIPPING_ALIAS . ".parent_id" .
            " AND " . self::SHIPPING_ALIAS . ".address_type = 'shipping')",
            [
                self::SHIP_TO_FIRST_NAME_FIELD,
                self::SHIP_TO_LAST_NAME_FIELD
            ]
        );
        $collection->addExpressionFieldToSelect(
            "bill_to_name",
            "CONCAT({{bill_firstname}}, ' ', {{bill_lastname}})",
            ["bill_firstname" => self::BILL_TO_FIRST_NAME_FIELD, "bill_lastname" => self::BILL_TO_LAST_NAME_FIELD]
        );

        $collection->addExpressionFieldToSelect(
            "ship_to_name",
            "CONCAT({{ship_firstname}}, ' ', {{ship_lastname}})",
            ["ship_firstname" => self::SHIP_TO_FIRST_NAME_FIELD, "ship_lastname" => self::SHIP_TO_LAST_NAME_FIELD]
        );
    }

    /**
     * @param $collection
     */
    public function joinVendorCommissionPaymentTable($collection)
    {
        $collection->getSelect()->joinLeft(
            ["rvp" => "md_vendor_commission_payment"],
            "main_table.vendor_order_id = rvp.vendor_order_id",
            ["rvp.cancellation_fee"]
        );
    }

    /**
     * @param $collection
     * @param $q
     */
    public function addSearchFilterToCollection($collection, $q)
    {
        $str = preg_replace('/[^A-Za-z0-9\. -]/', '', $q);
        $collection->getSelect()->where("main_table.increment_id LIKE '%" . trim($str) . "%' OR CONCAT_WS(' '," . self::BILL_TO_FIRST_NAME_FIELD . "," . self::BILL_TO_LAST_NAME_FIELD . ") LIKE '%" . trim($str) . "%' OR CONCAT_WS(' '," . self::SHIP_TO_FIRST_NAME_FIELD . "," . self::SHIP_TO_LAST_NAME_FIELD . ") LIKE '%" . trim($str) . "%'");
    }
}
