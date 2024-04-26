<?php

namespace Ktpl\Tookan\Model\ResourceModel\ReturnsExport\Grid;

use Ktpl\Tookan\Helper\Data;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Sales\Model\ResourceModel\Order\Shipment;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    const RETURN_ID_PREFIX = "R";
    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        Data $tookanHelper,
        TimezoneInterface $timezone,
        $mainTable = 'magento_rma_grid',
        $resourceModel = Shipment::class
    ) {
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
        /**
         * Get Order Comment
         */
        $orderTable = $this->getTable('sales_order');

        $pickupBuffer = $this->tookanHelper->getPickupBuffer();
        $deliveryBuffer = $this->tookanHelper->getDeliveryBuffer();

        $pickupTime = $this->timezone->date()->modify("$pickupBuffer hours")->format("m/d/Y H:i:s");
        $deliveryTime = $this->timezone->date()->modify("$deliveryBuffer hours")->format("m/d/Y H:i:s");

        $this->getSelect()->JoinInner(
            $orderTable . ' as order_table',
            'main_table.order_id = order_table.entity_id',
            [
                'order_comment' => 'IF(main_table.increment_id IS NULL,"","")',
                'p_order_id' => 'CONCAT("'.self::RETURN_ID_PREFIX.'", "", main_table.increment_id)',
                'd_order_id' => 'CONCAT("'.self::RETURN_ID_PREFIX.'", "", main_table.increment_id)',
                'has_pickup' => 'IF(main_table.increment_id IS NULL,0,1)',
                'pickup_time' => 'IF(main_table.increment_id IS NULL,"","' . $pickupTime . '")',
                'agent_id' => 'IF(main_table.increment_id IS NULL,"","")',
                'delivery_time' => 'IF(main_table.increment_id IS NULL,"","' . $deliveryTime . '")',
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
            'main_table.vendor_id = vendor_table.vendor_id',
            [
                'customer_email' => 'vendor_table.email',
                'pickup_mobile' => 'vendor_table.mobile'
            ]
        );

        $vendorDataTable = $this->getTable('md_vendor_website_data');

        $this->getSelect()->JoinInner(
            $vendorDataTable . ' as vendor_data_table',
            'main_table.vendor_id = vendor_data_table.vendor_id',
            [
                'delivery_street_address' => 'CONCAT(vendor_data_table.pickup_address1, " ", COALESCE(vendor_data_table.pickup_address2, ""))',
                'delivery_city' => 'vendor_data_table.pickup_city',
                'delivery_region' => 'vendor_data_table.pickup_region',
                'delivery_country' => 'IF(vendor_data_table.pickup_country_id = "IQ","Iraq",vendor_data_table.pickup_country_id)',
                'delivery_pincode' => 'IF(vendor_data_table.pickup_pincode IS NULL,"-",vendor_data_table.pickup_pincode)',
                'delivery_latitude' => 'vendor_data_table.pickup_latitude',
                'delivery_longitude' => 'vendor_data_table.pickup_longitude',
                'customer_name' => 'vendor_data_table.business_name'
            ]
        );

        /**
         * Get customer shipping address data
         */

        $shippingAddressTable = $this->getTable('sales_order_address');

        $this->getSelect()->JoinInner(
            $shippingAddressTable . ' as address_table',
            'main_table.order_id = address_table.parent_id and address_table.address_type="shipping"',
            [
                'pickup_street_address' => 'address_table.street',
                'pickup_city' => 'address_table.city',
                'pickup_region' => 'address_table.region',
                'pickup_country' => 'IF(address_table.country_id = "IQ","Iraq",address_table.country_id)',
                'pickup_pincode' => 'IF(address_table.postcode IS NULL,"-",address_table.postcode)',
                'vendor_mobile' => 'address_table.telephone',
                'vendor_name' => 'CONCAT(address_table.firstname, " ",address_table.lastname)',
                'vendor_email' => 'address_table.email'
            ]
        );

        $shippingAddressAttributeTable = $this->getTable('magento_customercustomattributes_sales_flat_order_address');

        $this->getSelect()->JoinInner(
            $shippingAddressAttributeTable . ' as address_attribute_table',
            'address_table.entity_id = address_attribute_table.entity_id',
            [
                'pickup_latitude' => 'address_attribute_table.latitude',
                'pickup_longitude' => 'address_attribute_table.longitude'
            ]
        );

        $this->getSelect()->where('main_table.status = "authorized"');
        parent::_renderFiltersBefore();
    }

    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('vendor_email', 'vendor_table.email');
        $this->addFilterToMap('vendor_name', 'vendor_data_table.business_name');
        $this->addFilterToMap('p_order_id', 'CONCAT("'.self::RETURN_ID_PREFIX.'", "", main_table.increment_id)');

        parent::_initSelect();
    }
}
