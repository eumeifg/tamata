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
namespace Magedelight\Sales\Model\Order\SplitOrder;

use Magedelight\Catalog\Model\ResourceModel\Product as VendorProductResource;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magedelight\Sales\Model\OrderFactory as VendorOrderFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product\Type;

/**
 * Process main order to get sub order data.
 * @author Rocket Bazaar Core Team
 * Created at 31 Dec , 2019 11:30:00 AM
 */
class OrderProcessor extends \Magento\Framework\DataObject
{
    /**
     * @var integer
     */
    protected $totalItems = 0;

    /**
     * @var VendorOrderFactory
     */
    protected $vendorOrderFactory;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var VendorProductResource
     */
    protected $vendorProductResource;

    /**
     * ProcessOrder constructor.
     * @param EventManagerInterface $eventManager
     * @param LoggerInterface $logger
     * @param VendorProductResource $vendorProductResource
     */
    public function __construct(
        EventManagerInterface $eventManager,
        LoggerInterface $logger,
        VendorProductResource $vendorProductResource
    ) {
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->vendorProductResource = $vendorProductResource;
    }

    /**
     * @return int
     */
    public function getTotalItems()
    {
        return $this->getData('total_items');
    }

    /**
     * @param int $totalItems
     * @return SplitOrder
     */
    public function setTotalItems($totalItems)
    {
        return $this->setData('total_items', $totalItems);
    }

    /**
     * @param $order
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function process($order)
    {
        $subOrders = [];
        $totalItems = 0;
        $this->setTotalItems(0);
        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {

            /* @var $item \Magento\Quote\Model\Quote\Item */
            $vendorId = $item->getData('vendor_id');
            if ($vendorId == null || $vendorId == '' || $vendorId == 0) {
                continue;
            }

            if (!in_array($item->getProductType(), [Configurable::TYPE_CODE])) {
                /* Exclude parent items.*/
                $totalItems += $item->getQtyOrdered();
            }

            /* decrease vendor product qty when order placed */
            $connection = $this->vendorProductResource->getConnection();
            $productTable = $this->vendorProductResource->getMainTable();

             /*if ($item->getProductType() === Configurable::TYPE_CODE) {
                foreach ($item->getChildrenItems() as $childItem) {
                    $connection->query("update {$productTable} SET qty = qty - {$childItem->getQtyOrdered()} where vendor_id = {$vendorId} AND marketplace_product_id = {$childItem->getProduct()->getEntityId()}");
                }
            } else {
                $connection->query("update {$productTable} SET qty = qty - {$item->getQtyOrdered()} where vendor_id = {$vendorId} AND marketplace_product_id = {$item->getProduct()->getEntityId()}");
            }*/

            /*considering only simple product. configurable product's child product condition wise it was decreasing
            double qty. than ordered Start*/
            if ($item->getProductType() === Type::DEFAULT_TYPE) {
            
                $connection->query("update {$productTable} SET qty = qty - {$item->getQtyOrdered()} where vendor_id = {$vendorId} AND marketplace_product_id = {$item->getProduct()->getEntityId()}");
            }
            /*considering only simple product. configurable product's child product condition wise it was decreasing
            double qty. than ordered End*/        

            $itemId = (!empty($item->getParentItem())) ?
                $item->getParentItem()->getQuoteItemId() :
                $item->getQuoteItemId();
            $index = ($this->splitVendorOrderItemWise()) ? $itemId : $vendorId;

            $this->eventManager->dispatch(
                'check_product_inventory',
                [
                    'vendor_id' => $vendorId,
                    'product_id'=>$item->getProduct()->getId()
                ]
            );

            if (!isset($subOrders[$index])) {
                $data = new \Magento\Framework\DataObject();
                $data->setData(
                    [
                        'order_id' => $order->getId(),
                        'vendor_id' => $vendorId,
                        'status' => VendorOrder::STATUS_PENDING,
                        'subtotal' => $item->getRowTotal(),
                        'base_subtotal' => $item->getBaseRowTotal(),
                        'base_subtotal_incl_tax' => $item->getBaseRowTotal(),
                        'subtotal_incl_tax' => $item->getRowTotalInclTax(),
                        'tax_amount' => $item->getTaxAmount(),
                        'base_tax_amount' => $item->getBaseTaxAmount(),
                        'giftwrap_amount' => $item->getGiftwrapperPrice(),
                        'items' => $item->getQtyOrdered(),
                        'grand_total' => $item->getRowTotal(),
                        'base_grand_total' => $item->getBaseRowTotal(),
                        'order_item' => $index,
                        'store_id' => $order->getStoreId(),
                        'main_order_increment_id' => $order->getIncrementId()
                    ]
                );
                $subOrders[$index] = $data;

                /* Event to add additional data to suborder. */
                $this->eventManager->dispatch(
                    'prepare_sub_order_data',
                    [
                        'vendor_id' => $vendorId,
                        'item' => $item,
                        'sub_order_data' => $subOrders[$index]
                    ]
                );
            }
        }
        $this->setTotalItems($totalItems);
        return $subOrders;
    }

    protected function splitVendorOrderItemWise()
    {
        return true;
    }
}
