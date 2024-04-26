<?php

namespace Ktpl\Tookan\Model\ResourceModel\OrderExport\Grid;

use Ktpl\Tookan\Helper\Data;
use Ktpl\Tookan\Model\Config\Source\TookanStatus;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Sales\Model\ResourceModel\Order\Shipment;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{

    const ORDER_ID_PREFIX = "D";
    /**
     * @var Data
     */
    private $tookanHelper;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Collection constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param Data $tookanHelper
     * @param TimezoneInterface $timezone
     * @param string $mainTable
     * @param string $resourceModel
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        Data $tookanHelper,
        TimezoneInterface $timezone,
        $mainTable = 'sales_shipment_grid',
        $resourceModel = Shipment::class
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->tookanHelper = $tookanHelper;
        $this->timezone = $timezone;
    }

    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Select::COLUMNS);
        $countSelect->columns('COUNT(main_table.entity_id)');
        return $countSelect;
    }

    protected function _renderFiltersBefore()
    {
        $shipmentTable = $this->getTable('sales_shipment');

        $pickupBuffer = $this->tookanHelper->getPickupBuffer();
        $deliveryBuffer = $this->tookanHelper->getDeliveryBuffer();

        $pickupTime = $this->timezone->date()->modify("$pickupBuffer hours")->format("m/d/Y H:i:s");
        $deliveryTime = $this->timezone->date()->modify("$deliveryBuffer hours")->format("m/d/Y H:i:s");

        $this->getSelect()->JoinInner(
            $shipmentTable . ' as shipment_table',
            'main_table.entity_id = shipment_table.entity_id',
            ['tookan_status',
                'shipment_entity_id' => 'shipment_table.entity_id'
            ]
        );

        $this->getSelect()->joinLeft(
            'md_vendor_order as sub_order',
            'shipment_table.vendor_order_id = sub_order.vendor_order_id',
            ['pickup_status']
        );

        /**
         * Get Order Comment
         */
        $orderTable = $this->getTable('sales_order');

        $this->getSelect()->JoinInner(
            $orderTable . ' as order_table',
            'main_table.order_id = order_table.entity_id',
            ['order_comment',
                'p_order_id' => 'CONCAT("' . self::ORDER_ID_PREFIX . '","",main_table.increment_id)',
                'd_order_id' => 'CONCAT("' . self::ORDER_ID_PREFIX . '","",main_table.increment_id)',
                'has_pickup' => 'IF(main_table.increment_id IS NULL,0,1)',
                'delivery_time' => 'IF(main_table.increment_id IS NULL,"","' . $deliveryTime . '")',
                'agent_id' => 'IF(main_table.increment_id IS NULL,"","")',
                'pickup_time' => 'IF(main_table.increment_id IS NULL,"","' . $pickupTime . '")',
                'tags' => 'IF(main_table.increment_id IS NULL,"","")'
            ]
        );

        /**
         * Get Vendor Details
         */
        $vendorTable = $this->getTable('md_vendor');

        $this->getSelect()->JoinInner(
            $vendorTable .
            ' as vendor_table',
            'shipment_table.vendor_id = vendor_table.vendor_id',
            [
                'vendor_email' => 'vendor_table.email',
                'vendor_mobile' => 'vendor_table.mobile',
                'vendor_id' => 'vendor_table.vendor_id'
            ]
        );

        $vendorDataTable = $this->getTable('md_vendor_website_data');

        $this->getSelect()->JoinInner(
            $vendorDataTable . ' as vendor_data_table',
            'shipment_table.vendor_id = vendor_data_table.vendor_id',
            [
                'pickup_street_address' => 'CONCAT(vendor_data_table.pickup_address1, " ", COALESCE(vendor_data_table.pickup_address2, ""))',
                'pickup_city' => 'vendor_data_table.pickup_city',
                'pickup_region' => 'vendor_data_table.pickup_region',
                'pickup_country' => 'IF(vendor_data_table.pickup_country_id = "IQ","Iraq",vendor_data_table.pickup_country_id)',
                'pickup_pincode' => 'IF(vendor_data_table.pickup_pincode IS NULL,"-",vendor_data_table.pickup_pincode)',
                'pickup_latitude' => 'vendor_data_table.pickup_latitude',
                'pickup_longitude' => 'vendor_data_table.pickup_longitude',
                'vendor_name' => 'vendor_data_table.business_name'
            ]
        );

        /**
         * Get customer shipping address data
         */

        $shippingAddressTable = $this->getTable('sales_order_address');

        $this->getSelect()->JoinInner(
            $shippingAddressTable . ' as address_table',
            'shipment_table.order_id = address_table.parent_id and address_table.address_type="shipping"',
            [
                'delivery_street_address' => 'address_table.street',
                'delivery_city' => 'address_table.city',
                'delivery_region' => 'address_table.region',
                'delivery_country' => 'IF(address_table.country_id = "IQ","Iraq",address_table.country_id)',
                'delivery_pincode' => 'IF(address_table.postcode IS NULL,"-",address_table.postcode)',
                'delivery_mobile' => 'address_table.telephone'
            ]
        );

        $shippingAddressAttributeTable = $this->getTable('magento_customercustomattributes_sales_flat_order_address');

        $this->getSelect()->JoinInner(
            $shippingAddressAttributeTable . ' as address_attribute_table',
            'address_table.entity_id = address_attribute_table.entity_id',
            [
                'delivery_latitude' => 'address_attribute_table.latitude',
                'delivery_longitude' => 'address_attribute_table.longitude'
            ]
        );
        $this->getSelect()->where('shipment_table.tookan_status = "' . TookanStatus::READY_TO_SHIPPED . '"');
        parent::_renderFiltersBefore();
    }

    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('vendor_email', 'vendor_table.email');
        $this->addFilterToMap('vendor_name', 'vendor_data_table.business_name');
        $this->addFilterToMap('p_order_id', 'CONCAT("' . self::ORDER_ID_PREFIX . '","",main_table.increment_id)');

        parent::_initSelect();
    }
}
