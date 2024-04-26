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
namespace MDC\Sales\Controller\Sellerhtml\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Description of exportPost
 *
 * @author Rocket Bazaar Core Team
 */
class ExportPost extends \Magedelight\Sales\Controller\Sellerhtml\Order\ExportPost
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * 
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->authSession = $authSession;
        $this->vendorOrderFactory = $vendorOrderFactory;
        parent::__construct($context,$layoutFactory,$fileFactory);
    }

    /**
     * Export action from import/export vendor order transaction
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $flag = 0;
        if ($this->getRequest()->getParam('sfrm') == "new") {
            $filename = "tobe_confirmed_orders.csv";
            $orderstatus = "pending";
            $block = $this->layoutFactory->create()->createBlock('Magedelight\Sales\Block\Sellerhtml\Vendor\Order');
            $collection = $block->getNewOrders();
        } elseif ($this->getRequest()->getParam('sfrm') == "confirmed") {
            $filename = "new_orders.csv";
            $orderstatus = "processing";
            $block = $this->layoutFactory->create()->createBlock('Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Confirmedorder');
            $collection = $block->getConfirmOrders();
        } elseif ($this->getRequest()->getParam('sfrm') == "packed") {
            $filename = "packed_orders.csv";
            $orderstatus = "packed";
            $block = $this->layoutFactory->create()->createBlock('Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Pack');
            $collection = $block->getPackedOrders();
        } else if ($this->getRequest()->getParam('sfrm') == "handover") {
            $filename = "handover_orders.csv";
            $block = $this->layoutFactory->create()->createBlock('Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Handover');
            $collection = $block->getHandoverOrders();
        } else if ($this->getRequest()->getParam('sfrm') == "intransit") {
            $filename = "in_transit_orders.csv";
            $block = $this->layoutFactory->create()->createBlock('Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Intransit');
            $collection = $block->getIntransitOrders();
        } else if ($this->getRequest()->getParam('sfrm') == "complete") {
            $filename = "complete_orders.csv";
            $block = $this->layoutFactory->create()->createBlock('Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Complete');
            $collection = $block->getCompleteOrders();
        } else if ($this->getRequest()->getParam('sfrm') == "cancelled") {
            $filename = "cancelled_orders.csv";
            $block = $this->layoutFactory->create()->createBlock('Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Cancelorder');
            $collection = $block->getOrders();
             $flag = 1;
        } else {
            $filename = "closed_orders.csv";
            $block = $this->layoutFactory->create()->createBlock('Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Closed');
            $collection = $block->getClosedOrders();
            $flag = 2;
        }

        /** start csv content and set template */
        if ($flag == 1) {
            $headers = new \Magento\Framework\DataObject([
                'rvo_increment_id' => __('Vendor Order Id'),
                'rvo_created_at' => __('Purchase Date'),
                'ship_to_name' => __('Ship To Name'),
                'bill_to_name' => __('Bill To Name'),
                'cancellation_fee' => __('Cancellation Fee'),
                'rvo_grand_total' => __('Grand Total'),
                'status' => __('Status'),
                'product_name' => __('Product Name'),
                'vendor_product' => __('Vendor Product'),
                'qty' => __('Qty'),
                'total' => __('Price'),
                'vendor_order_with_classification' => __('New Vendor Order ID')
            ]);
            $template = '"{{rvo_increment_id}}","{{rvo_created_at}}","{{ship_to_name}}","{{bill_to_name}}","{{cancellation_fee}}","{{rvo_grand_total}}","{{status}}","{{vendor_order_with_classification}}","{{product_name}}","{{vendor_product}}","{{qty}}","{{total}}","{{order_id}}"';
            $content = $headers->toString($template);
            $content .= "\n";

            foreach ($collection as $orders) {          
                foreach ($orders->getItems() as $item) {
                    if ($item->getVendorId() != $this->authSession->getUser()->getVendorId() || $item->getParentItem()) {
                        continue;
                    }
                    $vendorProduct  = $item->getVendorProduct($this->authSession->getUser()->getVendorId());
                    $vendorSkus[$item->getOrderId()."-".$item->getVendorOrderId()] = array('vendor_product_name' => $vendorProduct->getProductName(),'vendor_sku'=> $vendorProduct->getVendorSku(),'row_total'=> $item->getRowTotal(),'item_id'=>$item->getItemId(),'vendor_id' => $item->getVendorId(),'vendor_order_id' => $item->getVendorOrderId());
                }
            }

            while ($transaction = $collection->fetchItem()) {
                $get_date = explode(",", $transaction->toString($template));

                $nblock = $this->layoutFactory->create()->createBlock('Magedelight\Sales\Block\Sellerhtml\Vendor\Order');
                $rvo_created_at_new = $nblock->getCurrentLocaleDate(trim($get_date[1], '"'));
                $rvo_created_at = str_replace(',', '', $rvo_created_at_new);
                $rvo_increment_id = $get_date[0];
                $ship_to_name = $get_date[2];
                $bill_to_name = $get_date[3];
                $rvp_cancellation_fee = $get_date[4];
                $rvo_grand_total = $get_date[5];
                $status = $get_date[6];
                $new_rvo_increment_id = $get_date[7];
                $entity_id = (int)trim($get_date[12],'"');
                $new_string = $rvo_increment_id . "," . $rvo_created_at . "," . $ship_to_name . "," . 
                    $bill_to_name . ",". $rvp_cancellation_fee ."," . $rvo_grand_total . "," . $status . "," . $new_rvo_increment_id;
                $content .= $new_string;
                // $content .= $new_string . "\n";

                $exported_rvo_increment_id = explode("-", $rvo_increment_id);
                $vendor_order_id = (int)trim($exported_rvo_increment_id[1],'"') ;

                $order = $this->orderRepository->get($entity_id);
                foreach ($order->getAllItems() as $item) {
                    $vendorProduct  = $item->getVendorProduct($this->authSession->getUser()->getVendorId());
                    if(!$vendorProduct || $item->getParentItem()) {continue;}
                    // $order_string = "" . "," . "" . "," . "" . "," . "" . "," . "" . "," . "" . ",". "" .",". $vendorProduct->getVendorSku().",".$item->getQtyOrdered().",".$item->getRowTotal();
                    if($item->getVendorId() === $this->authSession->getUser()->getVendorId() && array_key_exists($item->getOrderId()."-".$vendor_order_id, $vendorSkus) && (int)$item->getVendorOrderId() === (int)$vendor_order_id ) {
                        $vendorData = $vendorSkus[$item->getOrderId()."-".$vendor_order_id];
                        //$order_string = "" . "," .$vendorData['vendor_product_name'].",".$vendorData['vendor_sku'].",".$item->getQtyOrdered().",".$vendorData['row_total'];
                        $order_string = "" . "," . str_replace(",", " ", $vendorData['vendor_product_name']) .",".$vendorData['vendor_sku'].",".$item->getQtyOrdered().",".$vendorData['row_total'];
                        $content .= $order_string . "\n";
                    }
                }
            }
        } elseif($flag == 2) {
            $headers = new \Magento\Framework\DataObject([
                'rvo_increment_id' => __('Vendor Order Id'),
                'rvo_created_at' => __('Purchase Date'),
                'ship_to_name' => __('Ship To Name'),
                'bill_to_name' => __('Bill To Name'),
                'total_refunded' => __('Total Refunded'),
                'rvo_grand_total' => __('Grand Total'),
                'status' => __('Status'),
                'product_name' => __('Product Name'),
                'vendor_product' => __('Vendor Product'),
                'qty' => __('Qty'),
                'total' => __('Price'),
                'vendor_order_with_classification' => __('New Vendor Order ID')
            ]);
            $template = '"{{rvo_increment_id}}","{{rvo_created_at}}","{{ship_to_name}}","{{bill_to_name}}","{{total_refunded}}","{{rvo_grand_total}}","{{status}}","{{vendor_order_with_classification}}","{{product_name}}","{{vendor_product}}","{{qty}}","{{total}}","{{order_id}}"';
            $content = $headers->toString($template);
            $content .= "\n";

            foreach ($collection as $orders) {
                foreach ($orders->getItems() as $item) {
                    if ($item->getVendorId() != $this->authSession->getUser()->getVendorId() || $item->getParentItem()) {
                        continue;
                    }
                    $vendorProduct  = $item->getVendorProduct($this->authSession->getUser()->getVendorId());
                    $vendorSkus[$item->getOrderId()."-".$item->getVendorOrderId()] = array('vendor_product_name' => $vendorProduct->getProductName(),'vendor_sku'=> $vendorProduct->getVendorSku(),'row_total'=> $item->getRowTotal(),'item_id'=>$item->getItemId(),'vendor_id' => $item->getVendorId(),'vendor_order_id' => $item->getVendorOrderId());
                }
            }

            while ($transaction = $collection->fetchItem()) {
                $get_date = explode(",", $transaction->toString($template));
                $nblock = $this->layoutFactory->create()->createBlock('Magedelight\Sales\Block\Sellerhtml\Vendor\Order');
                $rvo_created_at_new = $nblock->getCurrentLocaleDate(trim($get_date[1], '"'));
                $rvo_created_at = str_replace(',', '', $rvo_created_at_new);
                $rvo_increment_id = $get_date[0];
                $ship_to_name = $get_date[2];
                $bill_to_name = $get_date[3];
                $total_refunded = $get_date[4];
                $rvo_grand_total = $get_date[5];
                $status = $get_date[6];
                $new_rvo_increment_id = $get_date[7];
                $entity_id = (int)trim($get_date[12],'"');
                $new_string = $rvo_increment_id . "," . $rvo_created_at . "," . $ship_to_name . "," . 
                    $bill_to_name . ",". $total_refunded ."," . $rvo_grand_total . "," . $status . "," . $new_rvo_increment_id;
                $exported_rvo_increment_id = explode("-", $rvo_increment_id);
                $vendor_order_id = (int)trim($exported_rvo_increment_id[1],'"') ;
                // $content .= $new_string . "\n";
                $content .= $new_string;
                $order = $this->orderRepository->get($entity_id);
                foreach ($order->getAllItems() as $item){
                    $vendorProduct  = $item->getVendorProduct($this->authSession->getUser()->getVendorId());
                    if(!$vendorProduct || $item->getParentItem()){continue;}

                    // $order_string = "" . "," . "" . "," . "" . "," . "" . "," . "" . "," . "" . ",". "" .",". $vendorProduct->getVendorSku().",".$item->getQtyOrdered().",".$item->getRowTotal();
                    if($item->getVendorId() === $this->authSession->getUser()->getVendorId() && array_key_exists($item->getOrderId()."-".$vendor_order_id, $vendorSkus) && (int)$item->getVendorOrderId() === (int)$vendor_order_id ) {
                        $vendorData = $vendorSkus[$item->getOrderId()."-".$vendor_order_id];
                        $order_string = "" . "," . str_replace(",", " ", $vendorData['vendor_product_name']) . "," .$vendorData['vendor_sku'].",".$item->getQtyOrdered().",".$vendorData['row_total'];
                        $content .= $order_string . "\n";
                    }
                }
            }
        } else {
            $headers = new \Magento\Framework\DataObject([
                'rvo_increment_id' => __('Vendor Order Id'),
                'rvo_created_at' => __('Purchase Date'),
                'ship_to_name' => __('Ship To Name'),
                'bill_to_name' => __('Bill To Name'),
                'rvo_grand_total' => __('Grand Total'),
                'status' => __('Status'),
                'product_name' => __('Product Name'),
                'vendor_product' => __('Vendor Product'),
                'qty' => __('Qty'),
                'total' => __('Price'),
                'vendor_order_with_classification' => __('New Vendor Order ID')
            ]);

            $template = '"{{rvo_increment_id}}","{{rvo_created_at}}","{{ship_to_name}}","{{bill_to_name}}","{{rvo_grand_total}}","{{status}}","{{vendor_order_with_classification}}","{{product_name}}","{{vendor_product}}","{{qty}}","{{total}}","{{order_id}}"';
            $content = $headers->toString($template);
            $content .= "\n";

            foreach ($collection as $orders) {
                foreach ($orders->getItems() as $item) {
                    if ($item->getVendorId() != $this->authSession->getUser()->getVendorId() || $item->getParentItem()) {
                            continue;
                        }
                         $vendorProduct  = $item->getVendorProduct($this->authSession->getUser()->getVendorId());

                         $vendorSkus[$item->getOrderId()."-".$item->getVendorOrderId()] = array('vendor_product_name' => $vendorProduct->getProductName(),'vendor_sku'=> $vendorProduct->getVendorSku(),'row_total'=> $item->getRowTotal(),'item_id'=>$item->getItemId(),'vendor_id' => $item->getVendorId(),'vendor_order_id' => $item->getVendorOrderId());
                }
            }

            if($collection){
                while ($transaction = $collection->fetchItem()) {
                    $get_date = explode(",", $transaction->toString($template));

                    $nblock = $this->layoutFactory->create()->createBlock('Magedelight\Sales\Block\Sellerhtml\Vendor\Order');
                    $rvo_created_at_new = $nblock->getCurrentLocaleDate(trim($get_date[1], '"'));
                    $rvo_created_at = str_replace(',', '', $rvo_created_at_new);
                    $rvo_increment_id = $get_date[0];
                    $ship_to_name = $get_date[2];
                    $bill_to_name = $get_date[3];
                    $rvo_grand_total = $get_date[4];
                    $status = $get_date[5];
                    $new_rvo_increment_id = $get_date[6];
                    $entity_id = (int)trim($get_date[11],'"');
                 
                    $new_string = $rvo_increment_id . "," . $rvo_created_at . "," . $ship_to_name . "," . 
                        $bill_to_name . "," . $rvo_grand_total . "," . $status . "," . $new_rvo_increment_id;
                    // $content .= $new_string . "\n";
                    $content .= $new_string;
                    $order = $this->orderRepository->get($entity_id);
                    
                    $exported_rvo_increment_id = explode("-", $rvo_increment_id);
                    $vendor_order_id = (int)trim($exported_rvo_increment_id[1],'"') ;
                  
                    foreach ($order->getAllItems() as $item){
                        $vendorProduct  = $item->getVendorProduct($this->authSession->getUser()->getVendorId());
                         
                        if(!$vendorProduct || $item->getParentItem() ){
                         continue;   
                        }
                                          
                        // $order_string = "" . "," . "" . "," . "" . "," . "" . "," . "" . "," . "" . ",". $vendorProduct->getVendorSku().",".$item->getQtyOrdered().",".$item->getRowTotal();
                        // $vendorOrder = $this->vendorOrderFactory->create()->load($vendor_order_id);                        
                        
                        if($item->getVendorId() === $this->authSession->getUser()->getVendorId() && array_key_exists($item->getOrderId()."-".$vendor_order_id, $vendorSkus) && (int)$item->getVendorOrderId() === (int)$vendor_order_id ){   

                            $vendorData = $vendorSkus[$item->getOrderId()."-".$vendor_order_id];
                           
                            $order_string = "" . "," . str_replace(",", " ", $vendorData['vendor_product_name']) . "," .$vendorData['vendor_sku'].",".$item->getQtyOrdered().",".$vendorData['row_total'];
                           
                            $content .= $order_string . "\n";
                        }                     
                    }
                }
            }
        }
        return $this->fileFactory->create($filename, $content, DirectoryList::VAR_DIR);
    }
    
    protected function _isAllowed()
    {         
        return $this->_authorization->isAllowed('Magedelight_Sales::manage_orders');
    }
}