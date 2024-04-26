<?php

namespace Ktpl\BarcodeGenerator\Model;

use Exception;
use Ktpl\BarcodeGenerator\Helper\Data as BarcodeHelper;
use Ktpl\Tookan\Model\Config\Source\TookanStatus;
use Magedelight\Catalog\Model\Product;
use Magedelight\Sales\Model\OrderFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order;
use Magento\SalesSequence\Model\Sequence;

class Barcode extends AbstractModel
{
    /**
     * @var Product
     */
    private $vendorProduct;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var Shipment
     */
    private $shipmentModel;
    /**
     * @var OrderFactory
     */
    private $vendorOrderFactory;

    /**
     * Barcode constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Product $vendorProduct
     * @param ProductRepository $productRepository
     * @param Shipment $shipmentModel
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Product $vendorProduct,
        ProductRepository $productRepository,
        Shipment $shipmentModel,
        OrderFactory $vendorOrderFactory,
        Order $orderModel,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->vendorProduct = $vendorProduct;
        $this->productRepository = $productRepository;
        $this->shipmentModel = $shipmentModel;
        $this->vendorOrderFactory = $vendorOrderFactory;
        $this->orderModel = $orderModel;
    }

    public function getItemDataFromBarcode($barcodeString)
    {
        $itemData = [];
        if (strlen($barcodeString) < 9){
            $shipmentId = sprintf("%09s", $barcodeString);
        }else{
            $shipmentId = $barcodeString;
        }
        //$barcodeExplode = explode(BarcodeHelper::BARCODE_DELIMITER, $barcodeString);
        //$shipmentId = $barcodeExplode[0];
        //$shipmentId = substr($shipmentId, 1);

        //$subOrderId = '';
        //$mainOrderId = explode("-", $barcodeExplode[1])[0];
        //$subOrderId = $barcodeExplode[1];

        try {
            $shipment = $this->shipmentModel->loadByIncrementId($shipmentId);
            if ($shipment && $shipment->getId()) {

                $orderloadedp = $this->orderModel->load($shipment->getOrderId());
                $mainOrderId = $orderloadedp->getIncrementId();
                $orderIncrementId = $orderloadedp->getIncrementId();

//                $mainOrderId = $shipment->getOrder()->getIncrementId();
//                $orderIncrementId = $shipment->getOrder()->getIncrementId();
                $subOrderId = $orderIncrementId."-".$shipment->getVendorOrderId();

                $shipmentItems = $shipment->getItems();
                foreach ($shipmentItems as $key => $shipmentItem) {
					/*** removed as not in use ***/
                    //~ $productType = $shipmentItem->getOrderItem()->getProduct()->getTypeId();
					/*** removed as not in use ***/
					
                    /*if ($productType == "simple") {*/
                    
                    /*** removed as giving error and added  $shipmentItem->getQty() ***/
                        //~ $productOrderedQty = $shipmentItem->getOrderItem()->getQtyOrdered();
                    /*** removed as giving error and added  $shipmentItem->getQty() ***/
                    
                    /* price error */
//                        if ($productType == "configurable") {
//                            $vendorProductPrice = $shipmentItem->getPrice();
//                        } else {
//                            $vendorProductPrice = $shipmentItem->getOrderItem()->getVendorProduct()->getPrice();
//                        }
                        $itemData[$key]["main_order_id"] = $mainOrderId;
                        $itemData[$key]["sub_order_id"] = $subOrderId;
                        $itemData[$key]["product_name"] = $shipmentItem->getName();
                        $itemData[$key]["price"] = $shipmentItem->getPrice();
                        $itemData[$key]["qty"] =  $shipmentItem->getQty();
                        $itemData[$key]["product_id"] =  $shipmentItem->getProductId();
                        $itemData[$key]["main_order_status"] = "In Warehouse";
                        $itemData[$key]["sub_order_status"] = "Customer Delivery";
                        $itemData[$key]['error'] = false;
                        $itemData[$key]['item_id'] = $shipmentItem->getEntityId();
                    /*}*/
                }

                /*$vendorOrder = $this->vendorOrderFactory->create()->getCollection()->addFieldToFilter(
                    'order_id',
                    ['eq' => $shipment->getOrderId()]
                )->addFieldToFilter(
                    'vendor_id',
                    ['eq' => $shipment->getVendorId()]
                )->getFirstItem();*/
                //$subOrderId = $vendorOrder->getIncrementId();
            }
        } catch (Exception $e) {
            $itemData['error'] = true;
            $itemData['errorMessage'] = $e->getMessage();
        }

        //$vendorSku = $barcodeExplode[1];
        //$marketplaceSku = $barcodeExplode[2];
        //$qty = $barcodeExplode[3];
        //echo "<pre>";print_r($mainOrderId);die;

        /*try {
            //$vendorProduct = $this->vendorProduct->getVendorProductsBySku($vendorSku);

            //$marketplaceProduct = $this->productRepository->getById($vendorProduct->getMarketplaceProductId());


            //$itemData['product_id'] = $vendorProduct->getMarketplaceProductId();

        } catch (Exception $e) {

        }*/
        //echo "<pre>";print_r($itemData);die;
        return $itemData;
    }

    /**
     * @param $barcodeString
     * @param int $productLocation
     */
    public function updateTookanStatus($barcodeString, $productLocation = TookanStatus::IN_WAREHOUSE)
    {
        //$barcodeExplode = explode(BarcodeHelper::BARCODE_DELIMITER, $barcodeString);
        //$shipmentId = $barcodeExplode[0];
        //$shipmentId = substr($shipmentId, 1);
        $shipmentId = $barcodeString;
        try {
            $shipment = $this->shipmentModel->loadByIncrementId($shipmentId);
            if ($shipment && $shipment->getId()) {
                $shipment->setTookanStatus($productLocation)->save();
            }
        } catch (Exception $e) {
        }
    }
}
