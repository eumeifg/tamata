<?php

namespace MDC\Sales\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Magedelight\Vendor\Helper\Data;
use Psr\Log\LoggerInterface;

class VendorOrderUpdate implements ObserverInterface
{
    const IN_WAREHOUSE = 'in_warehouse';
    const ITEM_COMMISSION = 'item_commesion';
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $getConnection;

    /**
     * @var Data
     */
    protected $vendorHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * VendorOrderUpdate constructor.
     * @param ResourceConnection $resourceConnection
     * @param Data $vendorHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Data               $vendorHelper,
        LoggerInterface    $logger
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->getConnection = $this->resourceConnection->getConnection();
        $this->vendorHelper = $vendorHelper;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $VendorOrder = $observer->getEvent()->getVendorOrder();
            $order = $observer->getEvent()->getOrder();
            $date = date("dm", strtotime($order->getCreatedAt()));
            $result = $this->getIsWarrantyAndQty($VendorOrder, $order);
            $inWarehouse = $result['in_warehouse'];
            $qty = $result['qty'];
            $itemCommission = $result['commission'];
            $orderSize = $order->getTotalItemCount() > 1 ? 'M' : 'S';
            $finalId = $qty . '-' . $VendorOrder->getIncrementId() . $orderSize . $date . $inWarehouse;
            //$finalId = $VendorOrder->getIncrementId().'-'.$inWarehouse.'-'.$qty.'-'.substr($this->vendorHelper->getVendorNameById($VendorOrder->getVendorId()), 0,3).'-'.$orderSize;
            $VendorOrder->setVendorOrderWithClassification($finalId)->setItemCommission($itemCommission)->save();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param $isWarranty
     * @param $itemCount
     * @return string
     */
    public function getClassification($isWarranty, $itemCount)
    {
        if ($isWarranty === 1 && $itemCount === 1) {
            $classification = 'WS';
        } elseif ($isWarranty === 1 && $itemCount > 1) {
            $classification = 'WM';
        } elseif ($isWarranty === 0 && $itemCount === 1) {
            $classification = 'MS';
        } elseif ($isWarranty === 0 && $itemCount > 1) {
            $classification = 'MM';
        } else {
            $classification = 'MIX';
        }
        return $classification;
    }

    /**
     * @param $VendorOrder
     * @param $order
     * @return array
     */
    public function getIsWarrantyAndQty($VendorOrder, $order)
    {
        $inWarehouseIdSql = $this->getConnection->select()->from('eav_attribute', 'attribute_id')->where('attribute_code =?', self::IN_WAREHOUSE);
        $inWarehouseId = $this->getConnection->fetchOne($inWarehouseIdSql);
        $itemComSql = $this->getConnection->select()->from('eav_attribute', 'attribute_id')->where('attribute_code =?', self::ITEM_COMMISSION);
        $itemComId = $this->getConnection->fetchOne($itemComSql);

        $qty = 0;
        $inWarehouseArray = '';
        /** @var \Magento\Sales\Model\Order $order */
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            if (empty($item->getParentItem())) {
                $productId = $item->getProductId();
                $sql = $this->getConnection->select()->from(
                    ['cpe' => 'catalog_product_entity'],
                    'entity_id'
                )->joinLeft(
                    ['cpei' => 'catalog_product_entity_varchar'],
                    'cpei.row_id = cpe.row_id AND cpei.attribute_id = '.$inWarehouseId.' AND cpei.store_id = 0',
                    'value'
                )->joinLeft(
                    ['comm' => 'catalog_product_entity_varchar'],
                    'comm.row_id = cpe.row_id AND comm.attribute_id = '.$itemComId.' AND cpei.store_id = 0',
                    ['commission' => 'value']
                )->where('cpe.entity_id = ' . $productId);
                $queryResult = $this->getConnection->fetchRow($sql);
                $inWarehouseArray = !empty($queryResult['value']) ? $queryResult['value'] : 0;
                $itemCommissionValue = !empty($queryResult['commission']) ? $queryResult['commission'] : NULL;
            }
            if ($item->getVendorOrderId() === $VendorOrder->getVendorOrderId()) {
                $qty = $item->getQtyOrdered();
                $inWarehouse = $inWarehouseArray == 1 ? 'W' : 'P';
            }
        }
        return ['qty' => $qty, 'in_warehouse' => $inWarehouse, 'commission' => $itemCommissionValue];
    }
}
