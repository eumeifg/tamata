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

use Magento\Sales\Api\Data\OrderInterface;
use Magedelight\Sales\Api\Data\OrderItemImageInterface;
use Magento\Framework\App\ResourceConnection;

class OrderItemImages extends \Magedelight\Sales\Plugin\OrderItemImages
{

    /**
     * Recipient email config path
     */
    const XML_PATH_EMAIL_RECIPIENT = 'rma/policy/return_period';

    /**
     * @var OrderItemImageInterfaceFactory
     */
    protected $orderItemImageInterfaceFactory;

    /**
     * @var Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productFactory;

    /**
     * Rma data
     *
     * @var \Magento\Rma\Helper\Data
     */
    protected $_rmaData;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Rma\Model\ResourceModel\Item\CollectionFactory
     */
    protected $rmaItemFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * OrderItemImages constructor.
     * @param \Magedelight\Sales\Api\Data\OrderItemImageInterfaceFactory $orderItemImageInterfaceFactory
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Rma\Helper\Data $rmaData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Rma\Model\ResourceModel\Item\CollectionFactory $rmaItemFactory
     * @param \MDC\Rma\Helper\Data $mdcHelper
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magedelight\Sales\Api\Data\OrderItemImageInterfaceFactory $orderItemImageInterfaceFactory,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Rma\Helper\Data $rmaData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Rma\Model\ResourceModel\Item\CollectionFactory $rmaItemFactory,
        \MDC\Rma\Helper\Data $mdcHelper,
        ResourceConnection $resourceConnection
    ) {
        $this->orderItemImageInterfaceFactory = $orderItemImageInterfaceFactory;
        $this->productHelper = $productHelper;
        $this->productFactory = $productFactory;
        $this->_rmaData = $rmaData;
        $this->scopeConfig = $scopeConfig;
        $this->date = $date;
        $this->rmaItemFactory = $rmaItemFactory;
        $this->mdcHelper = $mdcHelper;
        $this->resourceConnection = $resourceConnection;
        $this->_connection = $this->resourceConnection->getConnection();
    }

    /**
     * Get Items
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function afterGetItems
    (
        \Magento\Sales\Api\Data\OrderInterface $subject,
        $result
    ) {
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
        foreach ($result as $item) {
            //echo ">>>>".$subject->getIncrementId().'-'.$item->getVendorOrderId(); die;
            $imageData = $this->orderItemImageInterfaceFactory->create();
            $imageData->setImageUrl($this->productHelper->getImageUrl(
                $item->getProduct()
            ));
            $extensionAttributes = $item->getExtensionAttributes();
            $extensionAttributes->setOrderItemImageData($imageData);

            /*....Start set return flason product level and return policy time in Day's....*/
            /*if($item->getProductType() != "simple") {
                continue;
            }*/

            $product = $item->getProduct();
            $productReturnFlag = $product->getIsReturnable();

            /*$product = $this->productFactory->create()->load($item->getProductId());
            $productReturnFlag = $this->_rmaData->canReturnProduct($product,$item->getStoreId());*/

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $returnPolicyValidity = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope);

            $orderCompletedData = $item->getUpdatedAt();
            $currentDate        = strtotime($this->date->gmtDate());
            $daysAddedDate      = strtotime(date('Y-m-d H:i:s', strtotime($orderCompletedData. ' + '.$returnPolicyValidity.' days')));

            if($daysAddedDate >= $currentDate) {
                $returnPolicyValidity = 1;
            } else {
                $returnPolicyValidity = 0;
            }

            $extensionAttributes->setProductRmaFlag($productReturnFlag);
            $extensionAttributes->setProductRmaValidity($returnPolicyValidity);

            $query = $this->_connection->select()
                ->from(['ssi' => 'sales_shipment_item'], 'order_item_id')
                ->joinLeft(['ss' => 'sales_shipment'], 'ss.entity_id = ssi.parent_id', 'increment_id')
                ->where('order_item_id=?', $item->getId());
            $queryResult = $this->_connection->fetchPairs($query);

            if (!empty($queryResult)) {
                $extensionAttributes->setProductShipmentId($queryResult[$item->getId()]);
            }

            /* Item SubOrder ID */
            $extensionAttributes->setItemIncrementId($subject->getIncrementId().'-'.$item->getVendorOrderId());
            /* Item Suborder Status */
            $suborderDetail = $this->getItemSuborderData($item->getVendorOrderId());
            if(isset($suborderDetail['status']) && !empty($suborderDetail['status'])) {
                $extensionAttributes->setItemStatus($suborderDetail['status']);
            }
            
            $cancelled_by = !empty($suborderDetail['cancelled_by']) ? $suborderDetail['cancelled_by'] : null;
            $extensionAttributes->setCancelledBy($cancelled_by);
            $is_packed = !empty($suborderDetail['is_packed']) ? $suborderDetail['is_packed'] : null;
            $extensionAttributes->setIsPacked($is_packed);
            $is_sorted = !empty($suborderDetail['is_sorted']) ? $suborderDetail['is_sorted'] : null;
            $extensionAttributes->setIsSorted($is_sorted);
            $is_picked_up = !empty($suborderDetail['is_picked_up']) ? $suborderDetail['is_picked_up'] : null;
            $extensionAttributes->setIsPickedUp($is_picked_up);
            $is_received = !empty($suborderDetail['is_received']) ? $suborderDetail['is_received'] : null;
            $extensionAttributes->setIsReceived($is_received);
            $ready_to_ship = !empty($suborderDetail['ready_to_ship']) ? $suborderDetail['ready_to_ship'] : null;
            $extensionAttributes->setReadyToShip($ready_to_ship);
            $barcode_number = !empty($suborderDetail['barcode_number']) ? $suborderDetail['barcode_number'] : null;
            $extensionAttributes->setBarcodeNumber($barcode_number);
            $vendor_order_with_classification = !empty($suborderDetail['vendor_order_with_classification']) ? $suborderDetail['vendor_order_with_classification'] : null;
            $extensionAttributes->setVendorOrderWithClassification($vendor_order_with_classification);
            $is_picked_up_timestamp = !empty($suborderDetail['is_picked_up_timestamp']) ? $suborderDetail['is_picked_up_timestamp'] : null;
            $extensionAttributes->setIsPickedUpTimestamp($is_picked_up_timestamp);
            $is_sorted_timestamp = !empty($suborderDetail['is_sorted_timestamp']) ? $suborderDetail['is_sorted_timestamp'] : null;
            $extensionAttributes->setIsSortedTimestamp($is_sorted_timestamp);
            $is_packed_timestamp = !empty($suborderDetail['is_packed_timestamp']) ? $suborderDetail['is_packed_timestamp'] : null;
            $extensionAttributes->setIsPackedTimestamp($is_packed_timestamp);
            $ready_to_ship_timestamp = !empty($suborderDetail['ready_to_ship_timestamp']) ? $suborderDetail['ready_to_ship_timestamp'] : null;
            $extensionAttributes->setReadyToShipTimestamp($ready_to_ship_timestamp);
            /*....End set return flason product level and return policy time in Day's....*/

            $item->setExtensionAttributes($extensionAttributes);
        }
        return $result;
    }
    
    public function getItemSuborderData($vendorOrderId) {
        $selectQuery = $this->_connection->select()->from(
            'md_vendor_order',
            ['status', 'cancelled_by', 'is_packed', 'is_sorted', 'is_picked_up', 'is_received', 'ready_to_ship', 'barcode_number', 'vendor_order_with_classification', 'is_picked_up_timestamp', 'is_sorted_timestamp', 'is_packed_timestamp', 'ready_to_ship_timestamp']
        )->where('vendor_order_id=?', $vendorOrderId);
        $result = $this->_connection->fetchRow($selectQuery);
        return $result;
    }
}
