<?php

namespace Ktpl\PromoCallouts\Model;

use Ktpl\PromoCallouts\Api\PromoCalloutsManagementInterface;
use Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magedelight\Sales\Model\Order as VendorOrder;

class PromoCalloutsManagement implements PromoCalloutsManagementInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * PromoCalloutsManagement constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return { @inheritDoc }
     */
    public function getPromoVendors()
    {
        $collection = $this->collectionFactory->create();

        $collection->getSelect()->join(
            ['mvwd' => 'md_vendor_website_data'],
            "mvwd.vendor_id = main_table.vendor_id AND mvwd.status = ".VendorStatus::VENDOR_STATUS_ACTIVE,
            ['business_name','name','logo','enable_microsite']
        );

        $collection->getSelect()->joinLeft(
            ['rbvrt' => 'md_vendor_order'],
            "main_table.vendor_id = rbvrt.vendor_id AND rbvrt.status = '".VendorOrder::STATUS_COMPLETE."'",
            ['rbvrt.status']
        );

        $collection->getSelect()->joinLeft(
            ['order_item' => 'sales_order_item'],
            "order_item.order_id = rbvrt.order_id",
            ['order_item.order_id']
        );
        $collection->getSelect()->joinLeft(
            ['prod' => 'catalog_product_entity'],
            "order_item.product_id = prod.entity_id",
            ['sku']
        )->distinct(true);

        $collection->getSelect()
            ->columns('SUM(order_item.qty_ordered) as total')
            ->group('main_table.vendor_id');
        $collection->getSelect()->where('main_table.is_system = 0')->limit(15);
        $collection->getSelect()->order("SUM(order_item.qty_ordered) DESC");
        return $collection->getItems();
    }
}
